from pydantic import BaseModel
from typing import Optional, List
from datetime import datetime

class AccessValidateRequest(BaseModel):
    vendor_face_id: str
    pic_face_id: str
    gate_id: str
    timestamp: Optional[datetime] = None
    # Embeddings from IoT device (now required for verification)
    vendor_embedding: Optional[List[float]] = None
    pic_embedding: Optional[List[float]] = None

class AccessValidateResponse(BaseModel):
    approved: bool
    reason: str
    similarity: Optional[float] = None
