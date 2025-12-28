from insightface.app import FaceAnalysis
import numpy as np
from typing import Optional, Tuple, List

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

    def detect_faces(self, frame: np.ndarray) -> List[dict]:
        """
        Detect all faces in the frame and return their info.
        Returns list of dicts with 'bbox', 'det_score', 'embedding'.
        """
        if not self.ready or self.app is None:
            return []
            
        faces = self.app.get(frame)
        if not faces:
            return []
        
        result = []
        for face in faces:
            bbox = face.bbox.astype(int).tolist()  # [x1, y1, x2, y2]
            det_score = float(face.det_score)
            emb = face.normed_embedding
            if emb is not None and emb.size > 0:
                emb = emb.astype(np.float32)
            else:
                emb = None
            result.append({
                'bbox': bbox,
                'det_score': det_score,
                'embedding': emb
            })
        return result

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
    
    def extract_embedding_with_bbox(self, frame: np.ndarray) -> Tuple[Optional[np.ndarray], Optional[List[int]]]:
        """
        Extract embedding for the largest face and return its bounding box.
        Returns (embedding, bbox) tuple where bbox is [x1, y1, x2, y2].
        """
        if not self.ready or self.app is None:
            return None, None
            
        faces = self.app.get(frame)
        if not faces:
            return None, None
            
        # Select the largest face by bounding box area
        face = max(faces, key=lambda f: (f.bbox[2]-f.bbox[0]) * (f.bbox[3]-f.bbox[1]))
        bbox = face.bbox.astype(int).tolist()
        emb = face.normed_embedding
        
        if emb is None or emb.size == 0:
            return None, bbox
            
        return emb.astype(np.float32), bbox

