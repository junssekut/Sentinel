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
# Theme Configuration - Light Theme with Blue Accent
# ============================================================

COLORS = {
    "bg_primary": "#FFFFFF",
    "bg_secondary": "#F5F7FA",
    "bg_card": "#FFFFFF",
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
    "shadow": "#00000015",
}

VIDEO_WIDTH = 580
VIDEO_HEIGHT = 360

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


class FaceClientApp:
    def __init__(self, root: tk.Tk):
        self.root = root
        self.root.title("Sentinel Face Recognition")
        self.root.protocol("WM_DELETE_WINDOW", self.on_close)
        self.root.configure(bg=COLORS["bg_primary"])
        self.root.resizable(True, True)
        
        # Center window on screen - more compact
        window_width = 680
        window_height = 620
        screen_width = root.winfo_screenwidth()
        screen_height = root.winfo_screenheight()
        x = (screen_width - window_width) // 2
        y = (screen_height - window_height) // 2
        self.root.geometry(f"{window_width}x{window_height}+{x}+{y}")
        
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
        # Main container with padding
        main_frame = tk.Frame(self.root, bg=COLORS["bg_primary"])
        main_frame.pack(fill=tk.BOTH, expand=True, padx=30, pady=25)
        
        # Header with logo/title
        header_frame = tk.Frame(main_frame, bg=COLORS["bg_primary"])
        header_frame.pack(fill=tk.X, pady=(0, 20))
        
        title_label = tk.Label(
            header_frame,
            text="🔐 Sentinel Access",
            font=get_font(28, "bold"),
            fg=COLORS["accent"],
            bg=COLORS["bg_primary"],
        )
        title_label.pack()
        
        subtitle_label = tk.Label(
            header_frame,
            text="Face Recognition Access Control",
            font=get_font(12),
            fg=COLORS["text_secondary"],
            bg=COLORS["bg_primary"],
        )
        subtitle_label.pack()
        
        # Video container with rounded border effect
        video_outer = tk.Frame(main_frame, bg=COLORS["border"], padx=2, pady=2)
        video_outer.pack()
        
        video_container = tk.Frame(video_outer, bg=COLORS["bg_card"])
        video_container.pack()
        
        self.video_label = tk.Label(
            video_container,
            bg=COLORS["bg_secondary"],
            width=VIDEO_WIDTH,
            height=VIDEO_HEIGHT,
        )
        self.video_label.pack(padx=8, pady=8)
        
        # Status bar with rounded appearance
        status_container = tk.Frame(main_frame, bg=COLORS["bg_primary"])
        status_container.pack(fill=tk.X, pady=(15, 15))
        
        self.status_frame = tk.Frame(
            status_container, 
            bg=COLORS["accent_light"],
            padx=20,
            pady=12,
        )
        self.status_frame.pack(fill=tk.X)
        
        self.status_var = tk.StringVar(value="Initializing...")
        self.status_label = tk.Label(
            self.status_frame,
            textvariable=self.status_var,
            font=get_font(12),
            fg=COLORS["accent"],
            bg=COLORS["accent_light"],
        )
        self.status_label.pack()
        
        # Form container
        form_frame = tk.Frame(main_frame, bg=COLORS["bg_primary"])
        form_frame.pack(fill=tk.X, pady=10)
        
        # Name input row
        name_row = tk.Frame(form_frame, bg=COLORS["bg_primary"])
        name_row.pack(fill=tk.X, pady=8)
        
        tk.Label(
            name_row,
            text="Name",
            font=get_font(13, "bold"),
            fg=COLORS["text_primary"],
            bg=COLORS["bg_primary"],
            width=6,
            anchor="w",
        ).pack(side=tk.LEFT)
        
        # Entry with border frame for rounded effect
        entry_border = tk.Frame(name_row, bg=COLORS["border"], padx=1, pady=1)
        entry_border.pack(side=tk.LEFT, padx=(10, 0))
        
        self.name_entry = tk.Entry(
            entry_border,
            font=get_font(12),
            bg=COLORS["bg_primary"],
            fg=COLORS["text_primary"],
            insertbackground=COLORS["accent"],
            relief=tk.FLAT,
            width=35,
        )
        self.name_entry.pack(ipady=10, ipadx=12)
        
        # Role selection row
        role_row = tk.Frame(form_frame, bg=COLORS["bg_primary"])
        role_row.pack(fill=tk.X, pady=8)
        
        tk.Label(
            role_row,
            text="Role",
            font=get_font(13, "bold"),
            fg=COLORS["text_primary"],
            bg=COLORS["bg_primary"],
            width=6,
            anchor="w",
        ).pack(side=tk.LEFT)
        
        self.role_var = tk.StringVar(value="vendor")
        
        role_options = tk.Frame(role_row, bg=COLORS["bg_primary"])
        role_options.pack(side=tk.LEFT, padx=(10, 0))
        
        for text, value in [("Vendor", "vendor"), ("PIC", "pic")]:
            rb = tk.Radiobutton(
                role_options,
                text=text,
                variable=self.role_var,
                value=value,
                font=get_font(12),
                fg=COLORS["text_primary"],
                bg=COLORS["bg_primary"],
                selectcolor=COLORS["accent_light"],
                activebackground=COLORS["bg_primary"],
                activeforeground=COLORS["accent"],
                highlightthickness=0,
                padx=10,
            )
            rb.pack(side=tk.LEFT, padx=(0, 15))
        
        # Buttons row with custom rounded buttons
        btn_frame = tk.Frame(main_frame, bg=COLORS["bg_primary"])
        btn_frame.pack(pady=20)
        
        self.enroll_btn = RoundedButton(
            btn_frame,
            text="📷 Enroll Face",
            command=self.enroll_face,
            bg_color=COLORS["accent"],
            fg_color="#FFFFFF",
            hover_color=COLORS["accent_hover"],
            width=180,
            height=48,
            radius=24,
            font_size=13,
        )
        self.enroll_btn.pack(side=tk.LEFT, padx=10)
        
        self.verify_btn = RoundedButton(
            btn_frame,
            text="⏸ Stop Verify",
            command=self.toggle_verify,
            bg_color=COLORS["bg_secondary"],
            fg_color=COLORS["text_primary"],
            hover_color=COLORS["border"],
            width=160,
            height=48,
            radius=24,
            font_size=12,
        )
        self.verify_btn.pack(side=tk.LEFT, padx=10)

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
        self.status_label.configure(fg=fg, bg=bg)
        self.status_frame.configure(bg=bg)

    def _start_session(self):
        try:
            resp = requests.post(f"{SERVER_URL}/api/session/start", json={}, timeout=5)
            if resp.status_code == 200:
                data = resp.json()
                self.session_id = data.get("session_id")
                self._update_status(f"Session active • Scan faces to verify", "info")
            else:
                self._update_status("Failed to start session", "warning")
        except Exception as exc:
            print(f"Session start error: {exc}")

    def _load_model(self):
        try:
            self._update_status(f"Loading AI model ({MODEL_NAME})...", "info")
            self.app = FaceAnalysis(name=MODEL_NAME, root=MODEL_DIR)
            self.app.prepare(ctx_id=-1, det_size=(DET_SIZE, DET_SIZE))
            self.model_ready = True
            self._update_status("✓ Model ready • Verification active", "success")
        except Exception as exc:
            if MODEL_NAME != "buffalo_s":
                try:
                    fallback = "buffalo_s"
                    self._update_status(f"Trying fallback model ({fallback})...", "warning")
                    self.app = FaceAnalysis(name=fallback, root=MODEL_DIR)
                    self.app.prepare(ctx_id=-1, det_size=(DET_SIZE, DET_SIZE))
                    self.model_ready = True
                    self._update_status("✓ Model ready (fallback)", "success")
                    return
                except Exception as exc_fb:
                    self._update_status(f"Model load failed: {exc_fb}", "error")
                    return
            self._update_status(f"Model load failed: {exc}", "error")

    def _monitor_model_load(self):
        if self.model_ready or not self.running:
            return
        elapsed = time.time() - self.model_start_time
        if elapsed > 60:
            self._update_status("Model loading slow. Check internet connection.", "warning")
        elif elapsed > 20:
            self._update_status("Loading model... (first run may take 20-60s)", "info")
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
            
            # Maintain aspect ratio
            img_width, img_height = img.size
            ratio = min(VIDEO_WIDTH / img_width, VIDEO_HEIGHT / img_height)
            new_size = (int(img_width * ratio), int(img_height * ratio))
            img = img.resize(new_size, Image.Resampling.LANCZOS)
            
            # Center on canvas with light gray background
            canvas = Image.new("RGB", (VIDEO_WIDTH, VIDEO_HEIGHT), COLORS["bg_secondary"])
            offset = ((VIDEO_WIDTH - new_size[0]) // 2, (VIDEO_HEIGHT - new_size[1]) // 2)
            canvas.paste(img, offset)
            
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
            self.verify_btn.set_text("⏸ Stop Verify")
            self._update_status("Verification resumed", "info")
        else:
            self.verify_btn.set_text("▶ Start Verify")
            self._update_status("Verification paused", "warning")

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
        
        self._update_status("Sending to server...", "info")
        
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
                    self._update_status(f"✓ Enrolled: {name}", "success")
                else:
                    self._update_status(f"Enrolled: {data.get('message', 'Success')}", "success")
            except requests.exceptions.HTTPError as exc:
                error_detail = ""
                try:
                    error_detail = exc.response.json().get('detail', str(exc))
                except:
                    error_detail = str(exc)
                self._update_status(f"Enroll failed: {error_detail}", "error")
            except Exception as exc:
                self._update_status(f"Enroll failed: {exc}", "error")
        
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
                self._update_status("Session expired. Restarting...", "warning")
                return
            
            if resp.status_code != 200:
                return

            data = resp.json()
            state = data.get("state")
            message = data.get("message", "")
            vendors = data.get("vendors", [])
            
            if state == "approved":
                self._update_status(f"✓ ACCESS GRANTED! {message}", "success")
                self.root.after(12000, self._start_session)
                self.session_id = None
            elif state == "waiting_pic":
                vendor_list = ", ".join(vendors) if vendors else "None"
                self._update_status(f"Vendors: {vendor_list} • Waiting for PIC...", "warning")
            elif state == "waiting_vendors":
                self._update_status(f"Scan vendor faces • {message}", "info")
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
