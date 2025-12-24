import base64
from typing import List
import numpy as np

def normalize(v: np.ndarray) -> np.ndarray:
    norm = np.linalg.norm(v)
    if norm == 0:
        return v
    return v / norm

def encode_embedding(vec: List[float]) -> bytes:
    arr = np.asarray(vec, dtype=np.float32)
    arr = normalize(arr)
    return base64.b64encode(arr.tobytes())

def decode_embedding(blob: bytes, dim: int) -> np.ndarray:
    raw = base64.b64decode(blob)
    arr = np.frombuffer(raw, dtype=np.float32)
    if dim and arr.size != dim:
        arr = arr[:dim]
    return normalize(arr)

def cosine_similarity(a: np.ndarray, b: np.ndarray) -> float:
    if a.size == 0 or b.size == 0:
        return 0.0
    return float(np.dot(normalize(a), normalize(b)))
