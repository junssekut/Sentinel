import os
import threading
import time
from pathlib import Path
from typing import Optional

# mitigate OpenMP DLL clashes on Windows
os.environ.setdefault("KMP_DUPLICATE_LIB_OK", "TRUE")
os.environ.setdefault("OMP_NUM_THREADS", "1")

import cv2
import base64
import numpy as np
import requests
from dotenv import load_dotenv
from insightface.app import FaceAnalysis
from PIL import Image, ImageTk, ImageDraw
import tkinter as tk
from tkinter import ttk, messagebox
import tkinter.font as tkfont

load_dotenv()

SERVER_URL = os.getenv("SERVER_URL", "http://127.0.0.1:8000")
API_SECRET = os.getenv("API_SECRET", "dev-secret")
DEVICE_ID = os.getenv("DEVICE_ID", "dev-1")
CAPTURE_INTERVAL = int(os.getenv("CAPTURE_INTERVAL", "5"))
CAMERA_INDEX = int(os.getenv("CAMERA_INDEX", "0"))
MODEL_NAME = os.getenv("MODEL_NAME", "buffalo_l")
DET_SIZE = int(os.getenv("DET_SIZE", "320"))

MODEL_DIR = Path.home() / ".insightface"

# ============================================================
# Theme Configuration
# ============================================================

COLORS = {
    "bg_window": "#F5F7FA",      # Light gray background for the window
    "bg_card": "#FFFFFF",        # White for cards
    "text_primary": "#1A1A2E",
    "text_secondary": "#6B7280",
    "accent": "#0066AE",
    "accent_hover": "#004D82",
    "accent_light": "#E6F0F8",
    "success": "#10B981",
    "success_bg": "#ECFDF5",
    "warning": "#F59E0B",
    "warning_bg": "#FFFBEB",
    "danger": "#EF4444",
    "danger_bg": "#FEF2F2",
    "border": "#E5E7EB",
    "shadow": "#00000015",      # Subtle shadow color
}

VIDEO_WIDTH = 640
VIDEO_HEIGHT = 480

# Custom font - try BricolageGrotesque, fallback to system fonts
FONT_FAMILY = "BricolageGrotesque"
FONT_FALLBACK = ("SF Pro Display", "Segoe UI", "Helvetica Neue", "Helvetica", "Arial")


def get_font(size: int, weight: str = "normal") -> tuple:
    """Get font tuple, trying custom font first then fallbacks"""
    return (FONT_FAMILY, size, weight)


class RoundedButton(tk.Canvas):
    """Custom rounded button using Canvas"""
    
    def __init__(self, parent, text, command, bg_color, fg_color, 
                 hover_color, width=160, height=44, radius=12, font_size=12, **kwargs):
        super().__init__(parent, width=width, height=height, 
                        bg=parent.cget("bg"), highlightthickness=0, **kwargs)
        
        self.command = command
        self.bg_color = bg_color
        self.fg_color = fg_color
        self.hover_color = hover_color
        self.current_bg = bg_color
        self.width = width
        self.height = height
        self.radius = radius
        self.text = text
        self.font_size = font_size
        
        self._draw()
        
        self.bind("<Enter>", self._on_enter)
        self.bind("<Leave>", self._on_leave)
        self.bind("<Button-1>", self._on_click)
    
    def _draw(self):
        self.delete("all")
        # Draw rounded rectangle
        self._create_rounded_rect(2, 2, self.width-2, self.height-2, 
                                  self.radius, fill=self.current_bg, outline="")
        # Draw text
        self.create_text(self.width//2, self.height//2, text=self.text,
                        fill=self.fg_color, font=get_font(self.font_size, "bold"))
    
    def _create_rounded_rect(self, x1, y1, x2, y2, r, **kwargs):
        points = [
            x1+r, y1, x2-r, y1,
            x2, y1, x2, y1+r,
            x2, y2-r, x2, y2,
            x2-r, y2, x1+r, y2,
            x1, y2, x1, y2-r,
            x1, y1+r, x1, y1,
        ]
        return self.create_polygon(points, smooth=True, **kwargs)
    
    def _on_enter(self, event):
        self.current_bg = self.hover_color
        self._draw()
        self.config(cursor="hand2")
    
    def _on_leave(self, event):
        self.current_bg = self.bg_color
        self._draw()
    
    def _on_click(self, event):
        if self.command:
            self.command()
    
    def set_text(self, text):
        self.text = text
        self._draw()


class BentoCard(tk.Canvas):
    """
    A 'Bento Grid' style card with rounded corners and a drop shadow.
    It contains a 'container' Frame where you place your widgets.
    """
    def __init__(self, parent, width, height, radius=20, bg_color="#FFFFFF", shadow_color="#00000010", shadow_offset=4, **kwargs):
        super().__init__(parent, width=width, height=height, 
                         bg=parent.cget("bg"), highlightthickness=0, **kwargs)
        
        self.width = width
        self.height = height
        self.radius = radius
        self.bg_color = bg_color
        self.shadow_color = shadow_color
        self.shadow_offset = shadow_offset
        
        # Draw the card graphics
        self._draw_card()
        
        # Create a frame inside to hold content
        # We start it at a slight offset so it fits inside the card area
        self.container = tk.Frame(self, bg=self.bg_color)
        
        # Calculate available area for the inner container so it doesn't overlap borders/shadows
        # x, y of the window in canvas coords
        container_x = shadow_offset + 2
        container_y = shadow_offset + 2 
        container_w = width - (shadow_offset * 3) 
        container_h = height - (shadow_offset * 3)
        
        self.create_window(width//2 - shadow_offset + 2, height//2 - shadow_offset + 2, 
                           window=self.container, 
                           width=container_w, height=container_h)

    def _draw_card(self):
        self.delete("all")
        
        # 1. Shadow Layer (Lower right offset)
        # We mimic a shadow by drawing a rounded rect in a transparent/grey color
        # Note: Canvas transparency is tricky. We'll use a solid color that matches background mixed with black, 
        # or just a light gray if actual alpha isn't perfect. Tkinter doesn't do real alpha on canvas items easily.
        # So we use a solid light gray color for shadow representation.
        shadow_x1 = self.shadow_offset
        shadow_y1 = self.shadow_offset
        shadow_x2 = self.width
        shadow_y2 = self.height
        
        self._create_rounded_rect(shadow_x1, shadow_y1, shadow_x2, shadow_y2, 
                                  self.radius, fill="#D1D5DB", outline="")

        # 2. Main Card Layer (Top left)
        card_x1 = 0
        card_y1 = 0
        card_x2 = self.width - self.shadow_offset
        card_y2 = self.height - self.shadow_offset
        
        self._create_rounded_rect(card_x1, card_y1, card_x2, card_y2, 
                                  self.radius, fill=self.bg_color, outline="")

    def _create_rounded_rect(self, x1, y1, x2, y2, r, **kwargs):
        points = [
            x1+r, y1, x2-r, y1,
            x2, y1, x2, y1+r,
            x2, y2-r, x2, y2,
            x2-r, y2, x1+r, y2,
            x1, y2, x1, y2-r,
            x1, y1+r, x1, y1,
        ]
        return self.create_polygon(points, smooth=True, **kwargs)


class FaceClientApp:
    def __init__(self, root: tk.Tk):
        self.root = root
        self.root.title("Sentinel Face Recognition")
        self.root.protocol("WM_DELETE_WINDOW", self.on_close)
        self.root.configure(bg=COLORS["bg_window"])
        
        # FIXED Window Size - Large and non-resizable as requested
        # 1100x720 is a good size to fit everything comfortably
        window_width = 1100
        window_height = 720
        self.root.geometry(f"{window_width}x{window_height}")
        self.root.resizable(False, False)
        
        # Center the window
        screen_width = root.winfo_screenwidth()
        screen_height = root.winfo_screenheight()
        pos_x = (screen_width - window_width) // 2
        pos_y = (screen_height - window_height) // 2
        self.root.geometry(f"+{pos_x}+{pos_y}")
        
        # Check if custom font is available
        self._check_fonts()
        
        # Build UI
        self._build_ui()
        
        # State
        self.last_frame: Optional[np.ndarray] = None
        self.running = True
        self.verify_running = True
        self.session_id: Optional[str] = None

        # Camera
        self.cap = cv2.VideoCapture(CAMERA_INDEX)
        if not self.cap.isOpened():
            messagebox.showerror("Camera", "Cannot open camera")
            raise SystemExit("camera not available")

        # Load model in background
        self.model_ready = False
        self.model_start_time = time.time()
        threading.Thread(target=self._load_model, daemon=True).start()
        self._monitor_model_load()

        self._schedule_frame_update()
        self._schedule_verify()
        
        # Start session
        threading.Thread(target=self._start_session, daemon=True).start()

    def _check_fonts(self):
        """Check if custom font is available"""
        available_fonts = tkfont.families()
        global FONT_FAMILY
        if FONT_FAMILY not in available_fonts:
            # Try fallbacks
            for fallback in FONT_FALLBACK:
                if fallback in available_fonts:
                    FONT_FAMILY = fallback
                    break
            else:
                FONT_FAMILY = "Helvetica"

    def _build_ui(self):
        """
        Builds a Bento Grid layout.
        Left Column: Video Feed
        Right Column: Header, Status (Big), Controls
        """
        # Main padding container
        main_pad = tk.Frame(self.root, bg=COLORS["bg_window"])
        main_pad.pack(fill=tk.BOTH, expand=True, padx=40, pady=40)
        
        # Configure grid for 2 columns
        # Left column (Video) takes more detail space slightly or equal
        main_pad.columnconfigure(0, weight=1) # Video
        main_pad.columnconfigure(1, weight=1) # Controls
        main_pad.rowconfigure(0, weight=1)

        # =========================================================
        # LEFT COLUMN (Video Feed)
        # =========================================================
        # We want the video card to take full height roughly
        video_card_h = 640
        video_card_w = 660 # Approx width
        
        self.video_card = BentoCard(main_pad, width=video_card_w, height=video_card_h, 
                                    bg_color=COLORS["bg_card"], radius=24)
        self.video_card.grid(row=0, column=0, sticky="nsew", padx=(0, 20))
        
        # Inside Video Card
        self.video_label = tk.Label(
            self.video_card.container,
            bg=COLORS["bg_window"], # Use a distinct bg for video placeholder
            text="Initializing Camera...",
            font=get_font(14),
            fg=COLORS["text_secondary"]
        )
        # We'll pack it to fill the card's container
        self.video_label.pack(expand=True, fill=tk.BOTH, padx=10, pady=10)

        # =========================================================
        # RIGHT COLUMN (Stack: Header, Status, Controls)
        # =========================================================
        right_col = tk.Frame(main_pad, bg=COLORS["bg_window"])
        right_col.grid(row=0, column=1, sticky="nsew")
        
        # 1. Header Card (Small height)
        header_h = 100
        header_w = 340 # auto width really, but passed to card init
        self.header_card = BentoCard(right_col, width=header_w, height=header_h, radius=20)
        self.header_card.pack(fill=tk.X, pady=(0, 20))
        
        # Content for Header
        header_inner = tk.Frame(self.header_card.container, bg=COLORS["bg_card"])
        header_inner.pack(fill=tk.BOTH, expand=True, padx=15)
        
        title_lbl = tk.Label(
            header_inner, 
            text="Sentinel Access", 
            font=get_font(24, "bold"), 
            fg=COLORS["accent"],
            bg=COLORS["bg_card"]
        )
        title_lbl.pack(side=tk.LEFT, pady=25)

        # 2. Status Card (Medium height, VERY BIG TEXT)
        status_h = 180
        self.status_card = BentoCard(right_col, width=header_w, height=status_h, 
                                     bg_color=COLORS["accent_light"], radius=20)
        self.status_card.pack(fill=tk.X, pady=(0, 20))
        
        self.status_var = tk.StringVar(value="Initializing...")
        self.status_label = tk.Label(
            self.status_card.container,
            textvariable=self.status_var,
            font=get_font(22, "bold"),  # Much bigger font as requested
            fg=COLORS["accent"],
            bg=COLORS["accent_light"],
            wraplength=300,
            justify="center"
        )
        self.status_label.pack(expand=True, fill=tk.BOTH, padx=10, pady=10)

        # 3. Controls Card (Remaining height)
        controls_h = 320
        self.controls_card = BentoCard(right_col, width=header_w, height=controls_h, radius=20)
        self.controls_card.pack(fill=tk.BOTH, expand=True) # Fill remaining vertical space
        
        # Content for Controls
        controls_inner = tk.Frame(self.controls_card.container, bg=COLORS["bg_card"])
        controls_inner.pack(fill=tk.BOTH, expand=True, padx=20, pady=20)
        
        # Name Input
        tk.Label(controls_inner, text="Name", font=get_font(12, "bold"), 
                 fg=COLORS["text_primary"], bg=COLORS["bg_card"]).pack(anchor="w")
        
        entry_frame = tk.Frame(controls_inner, bg=COLORS["border"], padx=1, pady=1)
        entry_frame.pack(fill=tk.X, pady=(5, 15))
        
        self.name_entry = tk.Entry(
            entry_frame,
            font=get_font(12),
            bg="#FFFFFF",
            relief=tk.FLAT
        )
        self.name_entry.pack(fill=tk.X, ipadx=5, ipady=8)

        # Role Input
        tk.Label(controls_inner, text="Role", font=get_font(12, "bold"), 
                 fg=COLORS["text_primary"], bg=COLORS["bg_card"]).pack(anchor="w")
        
        role_frame = tk.Frame(controls_inner, bg=COLORS["bg_card"])
        role_frame.pack(fill=tk.X, pady=(5, 20))
        
        self.role_var = tk.StringVar(value="vendor")
        for role, val in [("Vendor", "vendor"), ("PIC", "pic")]:
            rb = tk.Radiobutton(
                role_frame,
                text=role,
                variable=self.role_var,
                value=val,
                font=get_font(11),
                bg=COLORS["bg_card"],
                activebackground=COLORS["bg_card"]
            )
            rb.pack(side=tk.LEFT, padx=(0, 15))

        # Buttons
        btn_container = tk.Frame(controls_inner, bg=COLORS["bg_card"])
        btn_container.pack(fill=tk.X, pady=(10, 0))
        
        self.enroll_btn = RoundedButton(
            btn_container,
            text="Enroll Face",
            command=self.enroll_face,
            bg_color=COLORS["accent"],
            fg_color="#FFFFFF",
            hover_color=COLORS["accent_hover"],
            width=140,
            height=44,
            radius=12
        )
        self.enroll_btn.pack(side=tk.LEFT, padx=(0, 10))
        
        self.verify_btn = RoundedButton(
            btn_container,
            text="Stop Verify",
            command=self.toggle_verify,
            bg_color=COLORS["bg_window"],
            fg_color=COLORS["text_primary"],
            hover_color=COLORS["border"],
            width=140,
            height=44,
            radius=12
        )
        self.verify_btn.pack(side=tk.LEFT)

    def _update_status(self, message: str, status_type: str = "info"):
        """Update status with color based on type"""
        bg_map = {
            "info": COLORS["accent_light"],
            "success": COLORS["success_bg"],
            "warning": COLORS["warning_bg"],
            "error": COLORS["danger_bg"],
        }
        fg_map = {
            "info": COLORS["accent"],
            "success": COLORS["success"],
            "warning": COLORS["warning"],
            "error": COLORS["danger"],
        }
        self.status_var.set(message)
        bg = bg_map.get(status_type, COLORS["accent_light"])
        fg = fg_map.get(status_type, COLORS["accent"])
        
        # Update the label and the card background
        self.status_label.configure(fg=fg, bg=bg)
        
        # To update the BentoCard background, we need to redraw it
        self.status_card.bg_color = bg
        self.status_card._draw_card()
        # Also update the container frame bg
        self.status_card.container.configure(bg=bg)

    def _start_session(self):
        try:
            resp = requests.post(f"{SERVER_URL}/api/session/start", json={}, timeout=5)
            if resp.status_code == 200:
                data = resp.json()
                self.session_id = data.get("session_id")
                self._update_status(f"Session Active\nScan to Verify", "info")
            else:
                self._update_status("Failed to Start Session", "warning")
        except Exception as exc:
            print(f"Session start error: {exc}")

    def _load_model(self):
        try:
            self._update_status(f"Loading Model\n({MODEL_NAME})...", "info")
            self.app = FaceAnalysis(name=MODEL_NAME, root=MODEL_DIR)
            self.app.prepare(ctx_id=-1, det_size=(DET_SIZE, DET_SIZE))
            self.model_ready = True
            self._update_status("System Ready\nWaiting for Face", "success")
        except Exception as exc:
            if MODEL_NAME != "buffalo_s":
                try:
                    fallback = "buffalo_s"
                    self._update_status(f"Trying Fallback\n({fallback})...", "warning")
                    self.app = FaceAnalysis(name=fallback, root=MODEL_DIR)
                    self.app.prepare(ctx_id=-1, det_size=(DET_SIZE, DET_SIZE))
                    self.model_ready = True
                    self._update_status("System Ready\n(Fallback Mode)", "success")
                    return
                except Exception as exc_fb:
                    self._update_status(f"Load Failed:\n{exc_fb}", "error")
                    return
            self._update_status(f"Load Failed:\n{exc}", "error")

    def _monitor_model_load(self):
        if self.model_ready or not self.running:
            return
        elapsed = time.time() - self.model_start_time
        if elapsed > 60:
            self._update_status("Loading Slow\nCheck Internet", "warning")
        elif elapsed > 20:
            self._update_status("Still Loading...\n(Please Wait)", "info")
        self.root.after(2000, self._monitor_model_load)

    def _schedule_frame_update(self):
        if not self.running:
            return
        ret, frame = self.cap.read()
        if ret:
            self.last_frame = frame
            # Mirror the frame for natural preview
            mirrored = cv2.flip(frame, 1)
            rgb = cv2.cvtColor(mirrored, cv2.COLOR_BGR2RGB)
            img = Image.fromarray(rgb)
            
            # Smart Resize to fill the Video Card area
            # Get current size of the video container
            
            # Since we are inside a BentoCard, we check the container size
            # But the container might not be mapped yet on first run.
            # We use target card size as a baseline.
            target_w = 640
            target_h = 480
            
            # If the widget is mapped, use actual size
            if self.video_label.winfo_width() > 10:
                target_w = self.video_label.winfo_width()
                target_h = self.video_label.winfo_height()
            
            # Resize image to COVER the area (crop if needed) or contain?
            # User wants "Bento grid", usually implies filling the space nice.
            # Let's do Contain/Fit to avoid cutting off faces
            img_ratio = img.width / img.height
            target_ratio = target_w / target_h
            
            if img_ratio > target_ratio:
                # Image is wider -> match width
                new_w = target_w
                new_h = int(target_w / img_ratio)
            else:
                # Image is taller -> match height
                new_h = target_h
                new_w = int(target_h * img_ratio)
                
            img = img.resize((new_w, new_h), Image.Resampling.LANCZOS)
            
            # Create a background canvas to center the image
            canvas = Image.new("RGB", (target_w, target_h), "#000000")
            offset_x = (target_w - new_w) // 2
            offset_y = (target_h - new_h) // 2
            canvas.paste(img, (offset_x, offset_y))
            
            imgtk = ImageTk.PhotoImage(image=canvas)
            self.video_label.imgtk = imgtk
            self.video_label.configure(image=imgtk)
            
        self.root.after(30, self._schedule_frame_update)

    def _schedule_verify(self):
        if not self.running:
            return
        if self.verify_running:
            threading.Thread(target=self.verify_once, daemon=True).start()
        self.root.after(CAPTURE_INTERVAL * 1000, self._schedule_verify)

    def toggle_verify(self):
        self.verify_running = not self.verify_running
        if self.verify_running:
            self.verify_btn.set_text("Stop Verify")
            self._update_status("Verification Resumed", "info")
        else:
            self.verify_btn.set_text("Start Verify")
            self._update_status("Verification Paused", "warning")

    def _extract_embedding(self, frame: np.ndarray) -> Optional[np.ndarray]:
        if not self.model_ready:
            return None
        faces = self.app.get(frame)
        if not faces:
            return None
        face = max(faces, key=lambda f: f.bbox[2] * f.bbox[3])
        emb = face.normed_embedding
        if emb is None or emb.size == 0:
            return None
        return emb.astype(np.float32)

    def enroll_face(self):
        frame = self.last_frame
        if frame is None:
            messagebox.showwarning("Frame", "No frame available")
            return
        name = self.name_entry.get().strip()
        if not name:
            messagebox.showwarning("Input", "Name is required")
            return
        role = self.role_var.get()
        
        ret, buffer = cv2.imencode('.jpg', frame)
        face_image_b64 = base64.b64encode(buffer).decode('utf-8')
        face_image_data_uri = f"data:image/jpeg;base64,{face_image_b64}"

        payload = {
            "name": name,
            "role": role,
            "face_image": face_image_data_uri,
        }
        
        self._update_status("Sending to Server...", "info")
        
        def do_enroll():
            try:
                resp = requests.post(
                    f"{SERVER_URL}/api/faces/enroll-from-image",
                    json=payload,
                    timeout=30,
                )
                resp.raise_for_status()
                data = resp.json()
                status = data.get('status', 'unknown')
                if status == 'processing':
                    self._update_status(f"Enrolled:\n{name}", "success")
                else:
                    self._update_status(f"Enrolled:\n{data.get('message', 'Success')}", "success")
            except requests.exceptions.HTTPError as exc:
                error_detail = ""
                try:
                    error_detail = exc.response.json().get('detail', str(exc))
                except:
                    error_detail = str(exc)
                self._update_status(f"Enroll Failed:\n{error_detail}", "error")
            except Exception as exc:
                self._update_status(f"Enroll Failed:\n{exc}", "error")
        
        threading.Thread(target=do_enroll, daemon=True).start()

    def verify_once(self):
        frame = self.last_frame
        if frame is None or not self.model_ready:
            return
        if not self.session_id:
            self._start_session()
            return
            
        embedding = self._extract_embedding(frame)
        if embedding is None:
            return
            
        payload = {
            "session_id": self.session_id,
            "embedding": embedding.tolist(),
        }
        try:
            resp = requests.post(f"{SERVER_URL}/api/session/scan", json=payload, timeout=5)
            
            if resp.status_code == 404:
                self.session_id = None
                self._update_status("Session Expired\nRestarting...", "warning")
                return
            
            if resp.status_code != 200:
                return

            data = resp.json()
            state = data.get("state")
            message = data.get("message", "")
            vendors = data.get("vendors", [])
            
            if state == "approved":
                self._update_status(f"ACCESS GRANTED\n{message}", "success")
                self.root.after(12000, self._start_session)
                self.session_id = None
            elif state == "waiting_pic":
                vendor_list = ", ".join(vendors) if vendors else "None"
                self._update_status(f"Vendors Scanned\nWaiting for PIC...", "warning")
            elif state == "waiting_vendors":
                self._update_status(f"Scan Vendors\n{message}", "info")
            else:
                self._update_status(message, "info")
                
        except Exception as exc:
            print(f"Verify error: {exc}")

    def on_close(self):
        self.running = False
        time.sleep(0.2)
        if self.cap:
            self.cap.release()
        self.root.destroy()


def main():
    root = tk.Tk()
    FaceClientApp(root)
    root.mainloop()


if __name__ == "__main__":
    main()
