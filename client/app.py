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
    "bg_dark": "#0f0f1a",
    "bg_card": "#1a1a2e",
    "bg_input": "#252542",
    "text_primary": "#ffffff",
    "text_secondary": "#8888aa",
    "accent": "#00d4aa",
    "accent_hover": "#00b894",
    "danger": "#ff6b6b",
    "warning": "#feca57",
    "success": "#00d4aa",
    "border": "#2a2a4a",
}

VIDEO_WIDTH = 640
VIDEO_HEIGHT = 480


class FaceClientApp:
    def __init__(self, root: tk.Tk):
        self.root = root
        self.root.title("Sentinel Face Recognition")
        self.root.protocol("WM_DELETE_WINDOW", self.on_close)
        self.root.configure(bg=COLORS["bg_dark"])
        self.root.resizable(False, False)
        
        # Center window on screen
        window_width = 720
        window_height = 700
        screen_width = root.winfo_screenwidth()
        screen_height = root.winfo_screenheight()
        x = (screen_width - window_width) // 2
        y = (screen_height - window_height) // 2
        self.root.geometry(f"{window_width}x{window_height}+{x}+{y}")
        
        # Configure ttk styles
        self._setup_styles()
        
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

    def _setup_styles(self):
        style = ttk.Style()
        style.theme_use("clam")
        
        # Main button style
        style.configure(
            "Accent.TButton",
            background=COLORS["accent"],
            foreground=COLORS["bg_dark"],
            font=("Helvetica", 12, "bold"),
            padding=(20, 12),
            borderwidth=0,
        )
        style.map(
            "Accent.TButton",
            background=[("active", COLORS["accent_hover"])],
        )
        
        # Secondary button style
        style.configure(
            "Secondary.TButton",
            background=COLORS["bg_input"],
            foreground=COLORS["text_primary"],
            font=("Helvetica", 11),
            padding=(16, 10),
            borderwidth=0,
        )
        style.map(
            "Secondary.TButton",
            background=[("active", COLORS["border"])],
        )
        
        # Entry style
        style.configure(
            "Dark.TEntry",
            fieldbackground=COLORS["bg_input"],
            foreground=COLORS["text_primary"],
            insertcolor=COLORS["text_primary"],
            padding=10,
        )
        
        # Radiobutton style
        style.configure(
            "Dark.TRadiobutton",
            background=COLORS["bg_card"],
            foreground=COLORS["text_primary"],
            font=("Helvetica", 11),
            padding=8,
        )

    def _build_ui(self):
        # Main container
        main_frame = tk.Frame(self.root, bg=COLORS["bg_dark"])
        main_frame.pack(fill=tk.BOTH, expand=True, padx=20, pady=20)
        
        # Title
        title_label = tk.Label(
            main_frame,
            text="🔐 Sentinel Access",
            font=("Helvetica", 24, "bold"),
            fg=COLORS["text_primary"],
            bg=COLORS["bg_dark"],
        )
        title_label.pack(pady=(0, 15))
        
        # Video container with border effect
        video_container = tk.Frame(
            main_frame,
            bg=COLORS["border"],
            padx=3,
            pady=3,
        )
        video_container.pack()
        
        self.video_label = tk.Label(
            video_container,
            bg=COLORS["bg_card"],
            width=VIDEO_WIDTH,
            height=VIDEO_HEIGHT,
        )
        self.video_label.pack()
        
        # Status bar
        self.status_frame = tk.Frame(main_frame, bg=COLORS["bg_card"], height=50)
        self.status_frame.pack(fill=tk.X, pady=(15, 10))
        self.status_frame.pack_propagate(False)
        
        self.status_var = tk.StringVar(value="Initializing...")
        self.status_label = tk.Label(
            self.status_frame,
            textvariable=self.status_var,
            font=("Helvetica", 12),
            fg=COLORS["text_primary"],
            bg=COLORS["bg_card"],
            pady=12,
        )
        self.status_label.pack(fill=tk.X)
        
        # Form container
        form_frame = tk.Frame(main_frame, bg=COLORS["bg_dark"])
        form_frame.pack(fill=tk.X, pady=10)
        
        # Name input row
        name_row = tk.Frame(form_frame, bg=COLORS["bg_dark"])
        name_row.pack(fill=tk.X, pady=5)
        
        tk.Label(
            name_row,
            text="Name",
            font=("Helvetica", 12),
            fg=COLORS["text_secondary"],
            bg=COLORS["bg_dark"],
            width=8,
            anchor="e",
        ).pack(side=tk.LEFT, padx=(0, 10))
        
        self.name_entry = tk.Entry(
            name_row,
            font=("Helvetica", 12),
            bg=COLORS["bg_input"],
            fg=COLORS["text_primary"],
            insertbackground=COLORS["text_primary"],
            relief=tk.FLAT,
            width=30,
        )
        self.name_entry.pack(side=tk.LEFT, ipady=8, ipadx=8)
        
        # Role selection row
        role_row = tk.Frame(form_frame, bg=COLORS["bg_dark"])
        role_row.pack(fill=tk.X, pady=10)
        
        tk.Label(
            role_row,
            text="Role",
            font=("Helvetica", 12),
            fg=COLORS["text_secondary"],
            bg=COLORS["bg_dark"],
            width=8,
            anchor="e",
        ).pack(side=tk.LEFT, padx=(0, 10))
        
        self.role_var = tk.StringVar(value="vendor")
        
        role_options = tk.Frame(role_row, bg=COLORS["bg_dark"])
        role_options.pack(side=tk.LEFT)
        
        for text, value in [("Vendor", "vendor"), ("PIC", "pic")]:
            rb = tk.Radiobutton(
                role_options,
                text=text,
                variable=self.role_var,
                value=value,
                font=("Helvetica", 11),
                fg=COLORS["text_primary"],
                bg=COLORS["bg_dark"],
                selectcolor=COLORS["bg_input"],
                activebackground=COLORS["bg_dark"],
                activeforeground=COLORS["accent"],
                highlightthickness=0,
            )
            rb.pack(side=tk.LEFT, padx=10)
        
        # Buttons row
        btn_frame = tk.Frame(main_frame, bg=COLORS["bg_dark"])
        btn_frame.pack(pady=15)
        
        self.enroll_btn = tk.Button(
            btn_frame,
            text="📷  Enroll Face",
            font=("Helvetica", 12, "bold"),
            bg=COLORS["accent"],
            fg=COLORS["bg_dark"],
            activebackground=COLORS["accent_hover"],
            activeforeground=COLORS["bg_dark"],
            relief=tk.FLAT,
            padx=25,
            pady=12,
            cursor="hand2",
            command=self.enroll_face,
        )
        self.enroll_btn.pack(side=tk.LEFT, padx=8)
        
        self.verify_btn = tk.Button(
            btn_frame,
            text="⏸  Stop Verify",
            font=("Helvetica", 11),
            bg=COLORS["bg_input"],
            fg=COLORS["text_primary"],
            activebackground=COLORS["border"],
            activeforeground=COLORS["text_primary"],
            relief=tk.FLAT,
            padx=20,
            pady=12,
            cursor="hand2",
            command=self.toggle_verify,
        )
        self.verify_btn.pack(side=tk.LEFT, padx=8)

    def _update_status(self, message: str, status_type: str = "info"):
        """Update status with color based on type"""
        color_map = {
            "info": COLORS["text_primary"],
            "success": COLORS["success"],
            "warning": COLORS["warning"],
            "error": COLORS["danger"],
        }
        bg_map = {
            "info": COLORS["bg_card"],
            "success": "#0a2922",
            "warning": "#2a2510",
            "error": "#2a1515",
        }
        self.status_var.set(message)
        self.status_label.configure(
            fg=color_map.get(status_type, COLORS["text_primary"]),
            bg=bg_map.get(status_type, COLORS["bg_card"]),
        )
        self.status_frame.configure(bg=bg_map.get(status_type, COLORS["bg_card"]))

    def _start_session(self):
        try:
            resp = requests.post(f"{SERVER_URL}/api/session/start", json={}, timeout=5)
            if resp.status_code == 200:
                data = resp.json()
                self.session_id = data.get("session_id")
                self._update_status(f"Session: {self.session_id[:8]}... | Scan faces to verify", "info")
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
            self._update_status("✓ Model ready. Verification active.", "success")
        except Exception as exc:
            if MODEL_NAME != "buffalo_s":
                try:
                    fallback = "buffalo_s"
                    self._update_status(f"Trying fallback model ({fallback})...", "warning")
                    self.app = FaceAnalysis(name=fallback, root=MODEL_DIR)
                    self.app.prepare(ctx_id=-1, det_size=(DET_SIZE, DET_SIZE))
                    self.model_ready = True
                    self._update_status("✓ Model ready (fallback).", "success")
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
            
            # Center on canvas
            canvas = Image.new("RGB", (VIDEO_WIDTH, VIDEO_HEIGHT), COLORS["bg_card"])
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
            self.verify_btn.configure(text="⏸  Stop Verify")
            self._update_status("Verification resumed", "info")
        else:
            self.verify_btn.configure(text="▶  Start Verify")
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
                self._update_status(f"Vendors: {vendor_list} | Waiting for PIC...", "warning")
            elif state == "waiting_vendors":
                self._update_status(f"Scan vendor faces. {message}", "info")
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
