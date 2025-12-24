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
from PIL import Image, ImageTk
import tkinter as tk
from tkinter import messagebox

load_dotenv()

SERVER_URL = os.getenv("SERVER_URL", "http://127.0.0.1:8000")
API_SECRET = os.getenv("API_SECRET", "dev-secret")
DEVICE_ID = os.getenv("DEVICE_ID", "dev-1")
CAPTURE_INTERVAL = int(os.getenv("CAPTURE_INTERVAL", "5"))
CAMERA_INDEX = int(os.getenv("CAMERA_INDEX", "0"))
MODEL_NAME = os.getenv("MODEL_NAME", "buffalo_l")
DET_SIZE = int(os.getenv("DET_SIZE", "320"))

MODEL_DIR = Path.home() / ".insightface"  # cached models


class FaceClientApp:
    def __init__(self, root: tk.Tk):
        self.root = root
        self.root.title("Face Recognition Doorlock")
        self.root.protocol("WM_DELETE_WINDOW", self.on_close)

        self.video_label = tk.Label(root)
        self.video_label.pack()

        self.status_var = tk.StringVar(value="Initializing model (butuh internet saat pertama kali)...")
        tk.Label(root, textvariable=self.status_var, fg="blue").pack(pady=4)

        form_frame = tk.Frame(root)
        form_frame.pack(pady=4)

        tk.Label(form_frame, text="Nama").grid(row=0, column=0, sticky="e")
        self.name_entry = tk.Entry(form_frame, width=24)
        self.name_entry.grid(row=0, column=1, padx=4)

        tk.Label(form_frame, text="Perusahaan (vendor)").grid(row=1, column=0, sticky="e")
        self.company_entry = tk.Entry(form_frame, width=24)
        self.company_entry.grid(row=1, column=1, padx=4)

        role_frame = tk.Frame(root)
        role_frame.pack(pady=4)
        tk.Label(role_frame, text="Peran:").pack(side=tk.LEFT)
        self.role_var = tk.StringVar(value="vendor")
        tk.Radiobutton(role_frame, text="Vendor", variable=self.role_var, value="vendor").pack(side=tk.LEFT)
        tk.Radiobutton(role_frame, text="PIC", variable=self.role_var, value="pic").pack(side=tk.LEFT)

        btn_frame = tk.Frame(root)
        btn_frame.pack(pady=6)
        tk.Button(btn_frame, text="Enroll Muka", command=self.enroll_face).pack(side=tk.LEFT, padx=4)
        self.verify_btn = tk.Button(btn_frame, text="Stop Verifikasi", command=self.toggle_verify)
        self.verify_btn.pack(side=tk.LEFT, padx=4)

        self.last_frame: Optional[np.ndarray] = None
        self.running = True
        self.verify_running = True
        self.session_id: Optional[str] = None  # Session tracking

        self.cap = cv2.VideoCapture(CAMERA_INDEX)
        if not self.cap.isOpened():
            messagebox.showerror("Camera", "Tidak dapat membuka kamera")
            raise SystemExit("camera not available")

        # Load model in a thread to keep UI responsive
        self.model_ready = False
        self.model_start_time = time.time()
        threading.Thread(target=self._load_model, daemon=True).start()
        self._monitor_model_load()

        self._schedule_frame_update()
        self._schedule_verify()
        
        # Start a session on launch
        threading.Thread(target=self._start_session, daemon=True).start()

    def _start_session(self):
        """Start a new access session with the server"""
        try:
            resp = requests.post(f"{SERVER_URL}/api/session/start", json={}, timeout=5)
            if resp.status_code == 200:
                data = resp.json()
                self.session_id = data.get("session_id")
                self.status_var.set(f"Session started: {self.session_id}. Scan vendor faces.")
            else:
                self.status_var.set("Failed to start session")
        except Exception as exc:
            print(f"Session start error: {exc}")

    def _load_model(self):
        try:
            self.status_var.set(f"Memuat model {MODEL_NAME} di {MODEL_DIR} (det={DET_SIZE})...")
            self.app = FaceAnalysis(name=MODEL_NAME, root=MODEL_DIR)
            self.app.prepare(ctx_id=-1, det_size=(DET_SIZE, DET_SIZE))
            self.model_ready = True
            self.status_var.set("Model siap. Verifikasi berjalan...")
        except Exception as exc:
            if MODEL_NAME != "buffalo_s":
                # fallback ke model lebih kecil bila gagal
                try:
                    fallback = "buffalo_s"
                    self.status_var.set(f"Gagal load {MODEL_NAME}, coba {fallback}...")
                    self.app = FaceAnalysis(name=fallback, root=MODEL_DIR)
                    self.app.prepare(ctx_id=-1, det_size=(DET_SIZE, DET_SIZE))
                    self.model_ready = True
                    self.status_var.set("Model siap dengan fallback buffalo_s.")
                    return
                except Exception as exc_fb:  # pragma: no cover
                    self.status_var.set(f"Fallback gagal: {exc_fb}")
                    messagebox.showerror("Model", f"Fallback gagal: {exc_fb}")
                    return
            self.status_var.set(f"Gagal load model: {exc}")
            messagebox.showerror("Model", f"Gagal load model: {exc}")

    def _monitor_model_load(self):
        if self.model_ready or not self.running:
            return
        elapsed = time.time() - self.model_start_time
        if elapsed > 60:
            self.status_var.set("Model belum siap. Pastikan internet lancar untuk unduh pertama kali.")
        elif elapsed > 20:
            self.status_var.set("Masih memuat model, ini normal 20-60 detik di pertama kali...")
        self.root.after(2000, self._monitor_model_load)

    def _schedule_frame_update(self):
        if not self.running:
            return
        ret, frame = self.cap.read()
        if ret:
            self.last_frame = frame
            rgb = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
            img = Image.fromarray(rgb)
            img = img.resize((640, 480))
            imgtk = ImageTk.PhotoImage(image=img)
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
        text = "Stop Verifikasi" if self.verify_running else "Mulai Verifikasi"
        self.verify_btn.configure(text=text)

    def _extract_embedding(self, frame: np.ndarray) -> Optional[np.ndarray]:
        if not self.model_ready:
            self.status_var.set("Model belum siap...")
            return None
        faces = self.app.get(frame)
        if not faces:
            self.status_var.set("Wajah tidak terdeteksi")
            return None
        # pilih wajah terbesar
        face = max(faces, key=lambda f: f.bbox[2] * f.bbox[3])
        emb = face.normed_embedding
        if emb is None or emb.size == 0:
            self.status_var.set("Embedding kosong")
            return None
        return emb.astype(np.float32)

    def enroll_face(self):
        frame = self.last_frame
        if frame is None:
            messagebox.showwarning("Frame", "Frame belum tersedia")
            return
        name = self.name_entry.get().strip()
        if not name:
            messagebox.showwarning("Data", "Nama wajib diisi")
            return
        role = self.role_var.get()
        embedding = self._extract_embedding(frame)
        
        # Convert frame to base64 data URI for saving on server
        ret, buffer = cv2.imencode('.jpg', frame)
        face_image_b64 = base64.b64encode(buffer).decode('utf-8')
        face_image_data_uri = f"data:image/jpeg;base64,{face_image_b64}"

        if embedding is None:
            return
        payload = {
            "name": name,
            "role": role,
            "face_image": face_image_data_uri,  # Send as data URI for HTML <img> display
            "embedding": embedding.tolist(),
        }
        
        try:
            resp = requests.post(
                f"{SERVER_URL}/api/faces/enroll",
                params={"secret": API_SECRET},
                json=payload,
                timeout=10,
            )
            resp.raise_for_status()
            data = resp.json()
            self.status_var.set(f"Enroll berhasil: {data.get('name')} (User ID: {data.get('user_id')})")
        except Exception as exc:
            self.status_var.set(f"Enroll gagal: {exc}")
            messagebox.showerror("Enroll", f"Enroll gagal: {exc}")

    def verify_once(self):
        frame = self.last_frame
        if frame is None or not self.model_ready:
            return
        if not self.session_id:
            # No session, try to start one
            self._start_session()
            return
            
        embedding = self._extract_embedding(frame)
        if embedding is None:
            return
            
        # Session-based scan
        payload = {
            "session_id": self.session_id,
            "embedding": embedding.tolist(),
        }
        try:
            resp = requests.post(f"{SERVER_URL}/api/session/scan", json=payload, timeout=5)
            
            if resp.status_code == 404:
                # Session expired, start a new one
                self.session_id = None
                self.status_var.set("Session expired. Restarting...")
                return
            
            if resp.status_code != 200:
                self.status_var.set(f"Error: {resp.status_code}")
                return

            data = resp.json()
            state = data.get("state")
            message = data.get("message", "")
            vendors = data.get("vendors", [])
            
            # Update status based on session state
            if state == "approved":
                self.status_var.set(f"✅ ACCESS GRANTED! {message}")
                # Session completed, start a new one after a delay
                self.root.after(12000, self._start_session)  # Wait for door to close
                self.session_id = None
            elif state == "waiting_pic":
                vendor_list = ", ".join(vendors) if vendors else "None"
                self.status_var.set(f"Vendors: {vendor_list} | Waiting for PIC...")
            elif state == "waiting_vendors":
                self.status_var.set(f"Scan vendor faces. {message}")
            else:
                self.status_var.set(message)
                
        except Exception as exc:
            print(f"Verify loop error: {exc}")


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
