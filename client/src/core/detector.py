from insightface.app import FaceAnalysis
import numpy as np
from typing import Optional
from ..utils.helpers import MODEL_NAME, MODEL_DIR, DET_SIZE

class FaceDetector:
    def __init__(self, name=MODEL_NAME, root=MODEL_DIR):
        self.name = name
        self.root = root
        self.app = None
        self.ready = False

    def load(self, det_size=DET_SIZE):
        """Load and prepare the InsightFace model."""
        try:
            self.app = FaceAnalysis(name=self.name, root=self.root)
            self.app.prepare(ctx_id=-1, det_size=(det_size, det_size))
            self.ready = True
            return True
        except Exception as e:
            print(f"Error loading model {self.name}: {e}")
            return False

    def extract_embedding(self, frame: np.ndarray) -> Optional[np.ndarray]:
        """Extract the embedding for the largest face in the frame."""
        if not self.ready or self.app is None:
            return None
            
        faces = self.app.get(frame)
        if not faces:
            return None
            
        # Select the largest face by bounding box area
        face = max(faces, key=lambda f: (f.bbox[2]-f.bbox[0]) * (f.bbox[3]-f.bbox[1]))
        emb = face.normed_embedding
        
        if emb is None or emb.size == 0:
            return None
            
        return emb.astype(np.float32)
