import tkinter as tk
from tkinter import messagebox, ttk
import tkinter.font as tkfont
import threading
import time
import cv2
import numpy as np
from PIL import Image, ImageTk

from ..utils.helpers import (
    COLORS, ASSETS_DIR, CAMERA_INDEX, CAPTURE_INTERVAL, 
    DEVICE_ID, HEARTBEAT_INTERVAL,
    get_font, check_fonts
)
from .widgets import BentoCard
from ..core.api import SentinelAPI
from ..core.detector import FaceDetector


class FaceClientApp:
    """
    Clean verification-only client UI.
    No enrollment - face enrollment happens on the website.
    """
    
    def __init__(self, root: tk.Tk):
        self.root = root
        self.root.title("Sentinel Access Control")
        self.root.protocol("WM_DELETE_WINDOW", self.on_close)
        self.root.configure(bg=COLORS["bg_window"])
        
        # Compact window size
        window_width = 900
        window_height = 700
        self.root.geometry(f"{window_width}x{window_height}")
        self.root.resizable(False, False)
        
        # Center the window
        screen_width = root.winfo_screenwidth()
        screen_height = root.winfo_screenheight()
        pos_x = (screen_width - window_width) // 2
        pos_y = (screen_height - window_height) // 2
        self.root.geometry(f"+{pos_x}+{pos_y}")
        
        # Set window icon
        try:
            icon_path = ASSETS_DIR / "images" / "sentinel-logo.png"
            icon_img = Image.open(icon_path)
            self.icon_photo = ImageTk.PhotoImage(icon_img)
            self.root.iconphoto(True, self.icon_photo)
        except Exception as e:
            print(f"Could not set window icon: {e}")
        
        check_fonts()
        
        # Components
        self.api = SentinelAPI()
        self.detector = FaceDetector()
        
        # State
        self.last_frame = None
        self.running = True
        self.verify_running = True
        self.session_id = None
        self.session_expires_at = None
        self.detected_vendors = []
        self.current_step = 1  # 1 = waiting vendor, 2 = waiting PIC, 3 = completed
        self.last_wrong_pic_time = 0  # Track when wrong PIC was shown
        self.wrong_pic_cooldown = 3  # Seconds to suppress repeated wrong PIC errors
        
        # Build UI
        self._build_ui()
        
        # Camera
        self.cap = cv2.VideoCapture(CAMERA_INDEX)
        if not self.cap.isOpened():
            messagebox.showerror("Camera", "Cannot open camera")
            raise SystemExit("camera not available")

        # Load detector in background
        self.model_start_time = time.time()
        threading.Thread(target=self._load_model, daemon=True).start()
        self._monitor_model_load()

        self._schedule_frame_update()
        self._schedule_verify()
        
        # Start session
        threading.Thread(target=self._start_session, daemon=True).start()
        
        # Start heartbeat loop (if DEVICE_ID is configured)
        if DEVICE_ID:
            threading.Thread(target=self._heartbeat_loop, daemon=True).start()
        else:
            print("Warning: DEVICE_ID not set. Heartbeat disabled.")
        
        # Bring window to front
        self.root.lift()
        self.root.focus_force()

    def _heartbeat_loop(self):
        """Background loop to send heartbeats to the server."""
        while self.running:
            result = self.api.send_heartbeat(DEVICE_ID)
            if result.get("success"):
                print(f"Heartbeat OK: {DEVICE_ID}")
            else:
                print(f"Heartbeat failed: {result.get('error')}")
            time.sleep(HEARTBEAT_INTERVAL)

    def _build_ui(self):
        main_pad = tk.Frame(self.root, bg=COLORS["bg_window"])
        main_pad.pack(fill=tk.BOTH, expand=True, padx=30, pady=30)
        
        # Header
        header_frame = tk.Frame(main_pad, bg=COLORS["bg_window"])
        header_frame.pack(fill=tk.X, pady=(0, 20))
        
        try:
            logo_path = ASSETS_DIR / "images" / "sentinel-logo.png"
            pil_img = Image.open(logo_path)
            pil_img.thumbnail((40, 40), Image.Resampling.LANCZOS)
            self.logo_img = ImageTk.PhotoImage(pil_img)
            tk.Label(header_frame, image=self.logo_img, bg=COLORS["bg_window"]).pack(side=tk.LEFT, padx=(0, 12))
        except Exception:
            pass
            
        tk.Label(header_frame, text="Sentinel Access", font=get_font(22, "bold"), 
                 fg=COLORS["accent"], bg=COLORS["bg_window"]).pack(side=tk.LEFT)

        # Main content - two columns
        content_frame = tk.Frame(main_pad, bg=COLORS["bg_window"])
        content_frame.pack(fill=tk.BOTH, expand=True)
        content_frame.columnconfigure(0, weight=3)
        content_frame.columnconfigure(1, weight=2)
        
        # LEFT: Camera
        left_col = tk.Frame(content_frame, bg=COLORS["bg_window"])
        left_col.grid(row=0, column=0, sticky="nsew", padx=(0, 15))
        
        self.video_card = BentoCard(left_col, width=500, height=500, 
                                    bg_color=COLORS["bg_card"], radius=20)
        self.video_card.pack(fill=tk.BOTH, expand=True)
        
        self.video_label = tk.Label(
            self.video_card.container,
            bg="#1a1a2e",
            text="Initializing Camera...",
            font=get_font(14),
            fg=COLORS["text_secondary"]
        )
        self.video_label.pack(expand=True, fill=tk.BOTH, padx=8, pady=8)
        
        # Camera selector
        camera_frame = tk.Frame(left_col, bg=COLORS["bg_window"])
        camera_frame.pack(fill=tk.X, pady=(10, 0))
        
        self.available_cameras = self._detect_cameras()
        self.camera_var = tk.StringVar(value=self.available_cameras.get(CAMERA_INDEX, f"Camera {CAMERA_INDEX}"))
        
        camera_options = list(self.available_cameras.values()) if self.available_cameras else ["Camera 0"]
        self.camera_dropdown = ttk.Combobox(
            camera_frame, 
            textvariable=self.camera_var,
            values=camera_options,
            state="readonly",
            width=40
        )
        self.camera_dropdown.pack(fill=tk.X)
        self.camera_dropdown.bind("<<ComboboxSelected>>", self._on_camera_change)

        # RIGHT: Instructions and Status
        right_col = tk.Frame(content_frame, bg=COLORS["bg_window"])
        right_col.grid(row=0, column=1, sticky="nsew")
        
        # Status Card (prominent)
        self.status_card = BentoCard(right_col, width=300, height=100, 
                                     bg_color=COLORS["accent_light"], radius=16)
        self.status_card.pack(fill=tk.X, pady=(0, 15))
        
        self.status_var = tk.StringVar(value="Initializing...")
        self.status_label = tk.Label(
            self.status_card.container,
            textvariable=self.status_var,
            font=get_font(16, "bold"),
            fg=COLORS["accent"],
            bg=COLORS["accent_light"],
            wraplength=260,
            justify="center"
        )
        self.status_label.pack(expand=True, fill=tk.BOTH, padx=10, pady=10)

        # Instructions Card
        self.instructions_card = BentoCard(right_col, width=300, height=200, radius=16)
        self.instructions_card.pack(fill=tk.X, pady=(0, 15))
        
        instr_inner = tk.Frame(self.instructions_card.container, bg=COLORS["bg_card"])
        instr_inner.pack(fill=tk.BOTH, expand=True, padx=15, pady=12)
        
        tk.Label(instr_inner, text="Instructions", font=get_font(13, "bold"),
                 fg=COLORS["text_primary"], bg=COLORS["bg_card"]).pack(anchor="w", pady=(0, 10))
        
        # Step indicators
        self.step_frames = []
        for i, step_text in enumerate([
            "Scan Vendor Face(s)",
            "Scan PIC to Approve",
            "Access Result"
        ], 1):
            step_frame = tk.Frame(instr_inner, bg=COLORS["bg_card"])
            step_frame.pack(fill=tk.X, pady=3)
            
            self.step_indicator = tk.Label(
                step_frame, 
                text=f"{i}.", 
                font=get_font(12, "bold"),
                fg=COLORS["text_secondary"], 
                bg=COLORS["bg_card"],
                width=3
            )
            self.step_indicator.pack(side=tk.LEFT)
            
            step_label = tk.Label(
                step_frame,
                text=step_text,
                font=get_font(12),
                fg=COLORS["text_secondary"],
                bg=COLORS["bg_card"]
            )
            step_label.pack(side=tk.LEFT)
            
            self.step_frames.append((step_frame, self.step_indicator, step_label))
        
        # Hint label
        self.hint_var = tk.StringVar(value="Look at the camera to start")
        self.hint_label = tk.Label(
            instr_inner,
            textvariable=self.hint_var,
            font=get_font(10),
            fg=COLORS["accent"],
            bg=COLORS["bg_card"],
            wraplength=250
        )
        self.hint_label.pack(anchor="w", pady=(10, 0))

        # Session Info Card
        self.session_card = BentoCard(right_col, width=300, height=70, radius=16)
        self.session_card.pack(fill=tk.X, pady=(0, 15))
        
        session_inner = tk.Frame(self.session_card.container, bg=COLORS["bg_card"])
        session_inner.pack(fill=tk.BOTH, expand=True, padx=15, pady=10)
        
        self.session_info_var = tk.StringVar(value="No active session")
        tk.Label(session_inner, textvariable=self.session_info_var, font=get_font(11),
                 fg=COLORS["text_primary"], bg=COLORS["bg_card"]).pack(anchor="w")

        # Vendors Card
        self.vendors_card = BentoCard(right_col, width=300, height=90, radius=16)
        self.vendors_card.pack(fill=tk.X)
        
        vendors_inner = tk.Frame(self.vendors_card.container, bg=COLORS["bg_card"])
        vendors_inner.pack(fill=tk.BOTH, expand=True, padx=15, pady=10)
        
        tk.Label(vendors_inner, text="Vendors Detected", font=get_font(11, "bold"),
                 fg=COLORS["text_secondary"], bg=COLORS["bg_card"]).pack(anchor="w")
        
        self.vendors_list_var = tk.StringVar(value="None yet")
        tk.Label(vendors_inner, textvariable=self.vendors_list_var, font=get_font(12),
                 fg=COLORS["text_primary"], bg=COLORS["bg_card"], wraplength=250,
                 justify="left").pack(anchor="w")

    def _detect_cameras(self) -> dict:
        cameras = {}
        for i in range(5):
            cap = cv2.VideoCapture(i)
            if cap.isOpened():
                cameras[i] = f"Camera {i}"
                cap.release()
        return cameras if cameras else {0: "Camera 0"}

    def _on_camera_change(self, event):
        selected = self.camera_var.get()
        new_index = None
        for idx, name in self.available_cameras.items():
            if name == selected:
                new_index = idx
                break
        
        if new_index is not None and self.cap:
            self.cap.release()
            self.cap = cv2.VideoCapture(new_index)
            if self.cap.isOpened():
                self._update_status(f"Switched to Camera {new_index}", "info")
            else:
                self._update_status(f"Failed to open Camera {new_index}", "error")

    def _update_status(self, message: str, status_type: str = "info"):
        bg_map = {"info": COLORS["accent_light"], "success": COLORS["success_bg"],
                  "warning": COLORS["warning_bg"], "error": COLORS["danger_bg"]}
        fg_map = {"info": COLORS["accent"], "success": COLORS["success"],
                  "warning": COLORS["warning"], "error": COLORS["danger"]}
        
        self.status_var.set(message)
        bg = bg_map.get(status_type, COLORS["accent_light"])
        fg = fg_map.get(status_type, COLORS["accent"])
        self.status_label.configure(fg=fg, bg=bg)
        self.status_card.set_background_color(bg)

    def _update_step(self, step: int):
        """Update step indicators to show current progress."""
        self.current_step = step
        for i, (frame, indicator, label) in enumerate(self.step_frames, 1):
            if i < step:
                # Completed
                indicator.configure(text="[OK]", fg=COLORS["success"])
                label.configure(fg=COLORS["success"])
            elif i == step:
                # Current
                indicator.configure(text=f"{i}.", fg=COLORS["accent"])
                label.configure(fg=COLORS["text_primary"], font=get_font(12, "bold"))
            else:
                # Future
                indicator.configure(text=f"{i}.", fg=COLORS["text_secondary"])
                label.configure(fg=COLORS["text_secondary"], font=get_font(12))

    def _start_session(self):
        result = self.api.start_session()
        if result.get("success"):
            data = result.get("data")
            self.session_id = data.get("session_id")
            self.session_expires_at = time.time() + 60
            self.detected_vendors = []
            self.root.after(0, lambda: self._update_step(1))
            self.root.after(0, self._update_session_info)
            self.root.after(0, self._update_vendors_list)
            self._update_status("Ready\nScan Vendor Face", "info")
            self.hint_var.set("* Look at the camera to scan your face")
        else:
            error_msg = result.get("error", "Unknown error")
            if len(error_msg) > 60:
                error_msg = error_msg[:57] + "..."
            self._update_status(f"Connection Error\n{error_msg}", "error")
            self.session_info_var.set("No active session")

    def _update_session_info(self):
        if self.session_id and self.session_expires_at:
            remaining = int(self.session_expires_at - time.time())
            if remaining > 0:
                self.session_info_var.set(f"Session: {self.session_id[:8]}... | {remaining}s")
            else:
                self.session_info_var.set("Session expired • Restarting...")
                self.session_id = None
                self.session_expires_at = None
                self.detected_vendors = []
                self._update_vendors_list()
                self._update_step(1)
                self.root.after(1000, lambda: threading.Thread(target=self._start_session, daemon=True).start())
        else:
            self.session_info_var.set("No active session")

    def _update_vendors_list(self):
        if self.detected_vendors:
            self.vendors_list_var.set(", ".join(self.detected_vendors))
        else:
            self.vendors_list_var.set("None yet")

    def _load_model(self):
        self._update_status("Loading Model...", "info")
        if self.detector.load():
            self._update_status("Ready\nScan Vendor Face", "success")
        else:
            self._update_status("Model Load Failed", "error")

    def _monitor_model_load(self):
        if self.detector.ready or not self.running:
            return
        elapsed = time.time() - self.model_start_time
        if elapsed > 60:
            self._update_status("Loading Slow\nCheck Internet", "warning")
        elif elapsed > 20:
            self._update_status("Still Loading...\n(Please Wait)", "info")
        self.root.after(2000, self._monitor_model_load)

    def _schedule_frame_update(self):
        if not self.running: return
        ret, frame = self.cap.read()
        if ret:
            self.last_frame = frame
            mirrored = cv2.flip(frame, 1)
            rgb = cv2.cvtColor(mirrored, cv2.COLOR_BGR2RGB)
            img = Image.fromarray(rgb)
            
            target_w = self.video_label.winfo_width() if self.video_label.winfo_width() > 10 else 480
            target_h = self.video_label.winfo_height() if self.video_label.winfo_height() > 10 else 480
            
            img_ratio = img.width / img.height
            target_ratio = target_w / target_h
            
            if img_ratio > target_ratio:
                new_w, new_h = target_w, int(target_w / img_ratio)
            else:
                new_h, new_w = target_h, int(target_h * img_ratio)
                
            img = img.resize((new_w, new_h), Image.Resampling.LANCZOS)
            canvas = Image.new("RGB", (target_w, target_h), "#1a1a2e")
            canvas.paste(img, ((target_w - new_w) // 2, (target_h - new_h) // 2))
            
            imgtk = ImageTk.PhotoImage(image=canvas)
            self.video_label.imgtk = imgtk
            self.video_label.configure(image=imgtk)
            
        self.root.after(30, self._schedule_frame_update)

    def _schedule_verify(self):
        if not self.running: return
        if self.verify_running:
            threading.Thread(target=self.verify_once, daemon=True).start()
        self.root.after(CAPTURE_INTERVAL * 1000, self._schedule_verify)

    def verify_once(self):
        frame = self.last_frame
        if frame is None or not self.detector.ready: return
        if not self.session_id: return self._start_session()
            
        embedding = self.detector.extract_embedding(frame)
        if embedding is None: return
            
        payload = {"session_id": self.session_id, "embedding": embedding.tolist()}
        resp = self.api.scan_session(payload)
        
        if resp is None: return
        if resp.status_code == 404:
            self.session_id = self.session_expires_at = None
            self.detected_vendors = []
            self.root.after(0, self._update_session_info)
            self.root.after(0, self._update_vendors_list)
            self.root.after(0, lambda: self._update_step(1))
            self._update_status("Session Expired\nRestarting...", "warning")
            return
        
        if resp.status_code != 200: return
        data = resp.json()
        state, message, vendors = data.get("state"), data.get("message", ""), data.get("vendors", [])
        
        if vendors:
            self.detected_vendors = vendors
            self.root.after(0, self._update_vendors_list)
        
        self.root.after(0, self._update_session_info)
        
        if state == "approved":
            # Stop verification to show result
            self.verify_running = False
            
            self._update_status("[OK] ACCESS GRANTED", "success")
            self.root.after(0, lambda: self._update_step(3))
            self.hint_var.set("Door unlocking... Please enter")
            self.session_info_var.set("[OK] Session approved!")
            
            # Clear session after a delay to show the result
            def _clear_and_restart():
                self.detected_vendors = []
                self.session_id = None
                self.session_expires_at = None
                self.root.after(0, self._update_vendors_list)
                self.verify_running = True
                self._start_session()
            
            # Wait 8 seconds before starting new session
            self.root.after(8000, _clear_and_restart)
            
        elif state == "waiting_pic":
            # Check if this is an error (wrong PIC scanned)
            if "No task" in message or "not assigned" in message:
                # Wrong PIC detected - show error but KEEP scanning for correct PIC
                # Use cooldown to prevent spamming same error
                current_time = time.time()
                if current_time - self.last_wrong_pic_time > self.wrong_pic_cooldown:
                    # Show error with cooldown
                    self.last_wrong_pic_time = current_time
                    self._update_status("[X] Wrong PIC", "error")
                    self.hint_var.set(f"! {message}")
                # Don't stop verification - keep scanning for correct PIC!
                # Just update step indicator
                self.root.after(0, lambda: self._update_step(2))
            else:
                # Normal waiting for PIC - first time or vendor just scanned
                self.last_wrong_pic_time = 0  # Reset cooldown
                self._update_status("Vendors OK\nNow Scan PIC", "warning")
                self.root.after(0, lambda: self._update_step(2))
                self.hint_var.set("* PIC: Please scan your face to approve")
        elif state == "waiting_vendors":
            self.last_wrong_pic_time = 0  # Reset cooldown
            vendor_count = len(vendors)
            if vendor_count > 0:
                self._update_status(f"{vendor_count} Vendor(s) Scanned\nAdd more or scan PIC", "info")
                self.hint_var.set("* Scan another vendor or scan PIC to proceed")
            else:
                self._update_status("Ready\nScan Vendor Face", "info")
                self.hint_var.set("* Look at the camera to scan your face")
        else:
            # Handle denied or other messages
            if "DENIED" in message.upper():
                # Stop verification briefly to show denied message
                self.verify_running = False
                self._update_status("[X] ACCESS DENIED", "error")
                self.hint_var.set(f"! {message}")
                
                # Resume after 5 seconds with new session
                def _resume_verify():
                    self.verify_running = True
                    self.last_wrong_pic_time = 0
                    self._start_session()
                
                self.root.after(5000, _resume_verify)
            else:
                self._update_status(message, "info")

    def on_close(self):
        self.running = False
        if self.cap: self.cap.release()
        self.root.destroy()
