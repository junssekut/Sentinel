from pydantic import BaseModel
from typing import Optional, List
from datetime import datetime

class AccessValidateRequest(BaseModel):
    vendor_id: int
    pic_id: int
    gate_id: str
    timestamp: Optional[datetime] = None
    # Embeddings from IoT device (now required for verification)
    vendor_embedding: Optional[List[float]] = None
    pic_embedding: Optional[List[float]] = None

class UserCreate(BaseModel):
    name: str
    role: str # vendor, dcfm, soc
    face_image: Optional[str] = None # Base64 of the face (optional now)
    embedding: Optional[List[float]] = None # Pre-computed embedding from client

class IdentifyRequest(BaseModel):
    embedding: List[float]

class AccessValidateResponse(BaseModel):
    approved: bool
    reason: str
    similarity: Optional[float] = None

class HeartbeatRequest(BaseModel):
    device_id: str
