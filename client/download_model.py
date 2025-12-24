"""Pre-download InsightFace model to local cache so GUI can work offline.
Run: python download_model.py
Optional env: MODEL_NAME (default buffalo_l), DET_SIZE (default 320)
"""
import os
from pathlib import Path

# mitigate OpenMP DLL clashes on Windows
os.environ.setdefault("KMP_DUPLICATE_LIB_OK", "TRUE")
os.environ.setdefault("OMP_NUM_THREADS", "1")

from dotenv import load_dotenv
from insightface.app import FaceAnalysis

load_dotenv()

MODEL_NAME = os.getenv("MODEL_NAME", "buffalo_l")
DET_SIZE = int(os.getenv("DET_SIZE", "320"))
MODEL_DIR = Path.home() / ".insightface"


def main():
    print(f"Preparing model {MODEL_NAME} (det_size={DET_SIZE}) to cache {MODEL_DIR} ...")
    app = FaceAnalysis(name=MODEL_NAME, root=MODEL_DIR)
    app.prepare(ctx_id=-1, det_size=(DET_SIZE, DET_SIZE))
    print("Model ready and cached.")


if __name__ == "__main__":
    main()
