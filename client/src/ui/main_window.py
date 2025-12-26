import tkinter as tk
from tkinter import messagebox, ttk
import tkinter.font as tkfont
import threading
import time
import cv2
import base64
import numpy as np
from PIL import Image, ImageTk

from ..utils.helpers import (
    COLORS, ASSETS_DIR, CAMERA_INDEX, CAPTURE_INTERVAL, 
    get_font, check_fonts, VIDEO_WIDTH, VIDEO_HEIGHT
)
from .widgets import RoundedButton, BentoCard
from ..core.api import SentinelAPI
from ..core.detector import FaceDetector

class FaceClientApp:
    def __init__(self, root: tk.Tk):
        self.root = root
        self.root.title("Sentinel Face Recognition")
        self.root.protocol("WM_DELETE_WINDOW", self.on_close)
        self.root.configure(bg=COLORS["bg_window"])
        
        # FIXED Window Size
        window_width = 1100
        window_height = 820
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
        
        # Check fonts
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
        
        # Bring window to front
        self.root.lift()
        self.root.focus_force()

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

    def _build_ui(self):
        main_pad = tk.Frame(self.root, bg=COLORS["bg_window"])
        main_pad.pack(fill=tk.BOTH, expand=True, padx=40, pady=40)
        
        main_pad.columnconfigure(0, weight=1)
        main_pad.columnconfigure(1, weight=1)
        main_pad.rowconfigure(0, weight=1)

        # LEFT COLUMN
        left_col = tk.Frame(main_pad, bg=COLORS["bg_window"])
        left_col.grid(row=0, column=0, sticky="nsew", padx=(0, 20))
        
        video_card_h = 680
        video_card_w = 580
        
        self.video_card = BentoCard(left_col, width=video_card_w, height=video_card_h, 
                                    bg_color=COLORS["bg_card"], radius=24)
        self.video_card.pack(fill=tk.BOTH, expand=True)
        
        self.video_label = tk.Label(
            self.video_card.container,
            bg=COLORS["bg_window"],
            text="Initializing Camera...",
            font=get_font(14),
            fg=COLORS["text_secondary"]
        )
        self.video_label.pack(expand=True, fill=tk.BOTH, padx=10, pady=10)
        
        camera_frame = tk.Frame(left_col, bg=COLORS["bg_window"])
        camera_frame.pack(fill=tk.X, pady=(15, 0))
        
        tk.Label(camera_frame, text="Camera:", font=get_font(11), 
                 fg=COLORS["text_secondary"], bg=COLORS["bg_window"]).pack(side=tk.LEFT)
        
        self.available_cameras = self._detect_cameras()
        self.camera_var = tk.StringVar(value=self.available_cameras.get(CAMERA_INDEX, f"Camera {CAMERA_INDEX}"))
        
        camera_options = list(self.available_cameras.values()) if self.available_cameras else ["Camera 0"]
        self.camera_dropdown = ttk.Combobox(
            camera_frame, 
            textvariable=self.camera_var,
            values=camera_options,
            state="readonly",
            width=30
        )
        self.camera_dropdown.pack(side=tk.LEFT, padx=(10, 0))
        self.camera_dropdown.bind("<<ComboboxSelected>>", self._on_camera_change)

        # RIGHT COLUMN
        right_col = tk.Frame(main_pad, bg=COLORS["bg_window"])
        right_col.grid(row=0, column=1, sticky="nsew")
        
        # 1. Header
        self.header_card = BentoCard(right_col, width=400, height=100, radius=20)
        self.header_card.pack(fill=tk.X, pady=(0, 20))
        
        header_inner = tk.Frame(self.header_card.container, bg=COLORS["bg_card"])
        header_inner.pack(fill=tk.BOTH, expand=True, padx=15)
        
        logo_title_frame = tk.Frame(header_inner, bg=COLORS["bg_card"])
        logo_title_frame.pack(expand=True)
        
        try:
            logo_path = ASSETS_DIR / "images" / "sentinel-logo.png"
            pil_img = Image.open(logo_path)
            pil_img.thumbnail((50, 50), Image.Resampling.LANCZOS)
            self.logo_img = ImageTk.PhotoImage(pil_img)
            tk.Label(logo_title_frame, image=self.logo_img, bg=COLORS["bg_card"]).pack(side=tk.LEFT, padx=(0, 15))
        except Exception as e:
            print(f"Could not load logo: {e}")
            
        tk.Label(logo_title_frame, text="Sentinel Access", font=get_font(24, "bold"), 
                 fg=COLORS["accent"], bg=COLORS["bg_card"]).pack(side=tk.LEFT)

        # 2. Status
        self.status_card = BentoCard(right_col, width=400, height=100, 
                                     bg_color=COLORS["accent_light"], radius=20)
        self.status_card.pack(fill=tk.X, pady=(0, 15))
        
        self.status_var = tk.StringVar(value="Initializing...")
        self.status_label = tk.Label(
            self.status_card.container,
            textvariable=self.status_var,
            font=get_font(18, "bold"),
            fg=COLORS["accent"],
            bg=COLORS["accent_light"],
            wraplength=300,
            justify="center"
        )
        self.status_label.pack(expand=True, fill=tk.BOTH, padx=10, pady=5)

        # 3. Session Info
        self.session_card = BentoCard(right_col, width=400, height=80, radius=20)
        self.session_card.pack(fill=tk.X, pady=(0, 15))
        
        session_inner = tk.Frame(self.session_card.container, bg=COLORS["bg_card"])
        session_inner.pack(fill=tk.BOTH, expand=True, padx=15, pady=10)
        
        tk.Label(session_inner, text="Session", font=get_font(11, "bold"),
                 fg=COLORS["text_secondary"], bg=COLORS["bg_card"]).pack(anchor="w")
        
        self.session_info_var = tk.StringVar(value="No active session")
        tk.Label(session_inner, textvariable=self.session_info_var, font=get_font(12),
                 fg=COLORS["text_primary"], bg=COLORS["bg_card"]).pack(anchor="w")

        # 4. Vendors
        self.vendors_card = BentoCard(right_col, width=400, height=100, radius=20)
        self.vendors_card.pack(fill=tk.X, pady=(0, 15))
        
        vendors_inner = tk.Frame(self.vendors_card.container, bg=COLORS["bg_card"])
        vendors_inner.pack(fill=tk.BOTH, expand=True, padx=15, pady=10)
        
        tk.Label(vendors_inner, text="Vendors Detected", font=get_font(11, "bold"),
                 fg=COLORS["text_secondary"], bg=COLORS["bg_card"]).pack(anchor="w")
        
        self.vendors_list_var = tk.StringVar(value="None yet")
        tk.Label(vendors_inner, textvariable=self.vendors_list_var, font=get_font(12),
                 fg=COLORS["text_primary"], bg=COLORS["bg_card"], wraplength=280,
                 justify="left").pack(anchor="w")

        # 5. Controls
        self.controls_card = BentoCard(right_col, width=400, height=260, radius=20)
        self.controls_card.pack(fill=tk.BOTH, expand=True)
        
        controls_inner = tk.Frame(self.controls_card.container, bg=COLORS["bg_card"])
        controls_inner.pack(fill=tk.BOTH, expand=True, padx=20, pady=20)
        
        tk.Label(controls_inner, text="Name", font=get_font(12, "bold"), 
                 fg=COLORS["text_primary"], bg=COLORS["bg_card"]).pack(anchor="w")
        
        entry_frame = tk.Frame(controls_inner, bg=COLORS["border"], padx=1, pady=1)
        entry_frame.pack(fill=tk.X, pady=(5, 15))
        
        self.name_entry = tk.Entry(entry_frame, font=get_font(12), bg="#FFFFFF", relief=tk.FLAT)
        self.name_entry.pack(fill=tk.X, ipadx=5, ipady=8)

        tk.Label(controls_inner, text="Role", font=get_font(12, "bold"), 
                 fg=COLORS["text_primary"], bg=COLORS["bg_card"]).pack(anchor="w")
        
        role_frame = tk.Frame(controls_inner, bg=COLORS["bg_card"])
        role_frame.pack(fill=tk.X, pady=(5, 20))
        
        self.role_var = tk.StringVar(value="vendor")
        for role, val in [("Vendor", "vendor"), ("PIC", "pic")]:
            tk.Radiobutton(role_frame, text=role, variable=self.role_var, value=val,
                          font=get_font(11), bg=COLORS["bg_card"],
                          activebackground=COLORS["bg_card"]).pack(side=tk.LEFT, padx=(0, 15))

        btn_container = tk.Frame(controls_inner, bg=COLORS["bg_card"])
        btn_container.pack(fill=tk.X, pady=(10, 0))
        
        self.enroll_btn = RoundedButton(btn_container, text="Enroll Face", command=self.enroll_face,
                                       bg_color=COLORS["accent"], fg_color="#FFFFFF",
                                       hover_color=COLORS["accent_hover"], width=140, height=44)
        self.enroll_btn.pack(side=tk.LEFT, padx=(0, 10))
        
        self.verify_btn = RoundedButton(btn_container, text="Stop Verify", command=self.toggle_verify,
                                       bg_color=COLORS["bg_window"], fg_color=COLORS["text_primary"],
                                       hover_color=COLORS["border"], width=140, height=44)
        self.verify_btn.pack(side=tk.LEFT)

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

    def _start_session(self):
        result = self.api.start_session()
        if result.get("success"):
            data = result.get("data")
            self.session_id = data.get("session_id")
            self.session_expires_at = time.time() + 60
            self.detected_vendors = []
            self.root.after(0, self._update_session_info)
            self.root.after(0, self._update_vendors_list)
            self._update_status(f"Session Active\nScan to Verify", "info")
        else:
            error_msg = result.get("error", "Unknown error")
            # Truncate error if too long (max 80 chars)
            if len(error_msg) > 80:
                error_msg = error_msg[:77] + "..."
            self._update_status(f"Failed to create session:\n{error_msg}", "error")
            self.session_info_var.set("No active session")

    def _update_session_info(self):
        if self.session_id and self.session_expires_at:
            remaining = int(self.session_expires_at - time.time())
            if remaining > 0:
                self.session_info_var.set(f"ID: {self.session_id[:8]}... • Expires in {remaining}s")
            else:
                self.session_info_var.set("Session expired • Restarting...")
                self.session_id = None
                self.session_expires_at = None
                self.detected_vendors = []
                self._update_vendors_list()
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
            self._update_status("System Ready\nWaiting for Face", "success")
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
            
            target_w = self.video_label.winfo_width() if self.video_label.winfo_width() > 10 else 640
            target_h = self.video_label.winfo_height() if self.video_label.winfo_height() > 10 else 480
            
            img_ratio = img.width / img.height
            target_ratio = target_w / target_h
            
            if img_ratio > target_ratio:
                new_w, new_h = target_w, int(target_w / img_ratio)
            else:
                new_h, new_w = target_h, int(target_h * img_ratio)
                
            img = img.resize((new_w, new_h), Image.Resampling.LANCZOS)
            canvas = Image.new("RGB", (target_w, target_h), "#000000")
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

    def toggle_verify(self):
        self.verify_running = not self.verify_running
        self.verify_btn.set_text("Stop Verify" if self.verify_running else "Start Verify")
        self._update_status("Verification " + ("Resumed" if self.verify_running else "Paused"), 
                            "info" if self.verify_running else "warning")

    def enroll_face(self):
        frame = self.last_frame
        if frame is None: return messagebox.showwarning("Frame", "No frame available")
        name = self.name_entry.get().strip()
        if not name: return messagebox.showwarning("Input", "Name is required")
        
        _, buffer = cv2.imencode('.jpg', frame)
        face_image_b64 = base64.b64encode(buffer).decode('utf-8')
        payload = {
            "name": name,
            "role": self.role_var.get(),
            "face_image": f"data:image/jpeg;base64,{face_image_b64}",
        }
        
        self._update_status("Sending to Server...", "info")
        def do_enroll():
            try:
                res = self.api.enroll_face(payload)
                self._update_status(f"Enrolled:\n{name}", "success")
            except Exception as e:
                self._update_status(f"Enroll Failed:\n{e}", "error")
        threading.Thread(target=do_enroll, daemon=True).start()

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
            self._update_status(f"ACCESS GRANTED\n{message}", "success")
            self.detected_vendors = []
            self.root.after(0, self._update_vendors_list)
            self.session_info_var.set("Session approved!")
            self.root.after(12000, self._start_session)
            self.session_id = self.session_expires_at = None
        elif state == "waiting_pic":
            self._update_status(f"Vendors Scanned\nWaiting for PIC...", "warning")
        elif state == "waiting_vendors":
            self._update_status(f"Scan Vendors\n{message}", "info")
        else:
            self._update_status(message, "info")

    def on_close(self):
        self.running = False
        if self.cap: self.cap.release()
        self.root.destroy()
