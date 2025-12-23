from pydantic import BaseModel
from typing import Optional
from datetime import datetime

class AccessValidateRequest(BaseModel):
    vendor_face_id: str
    pic_face_id: str
    gate_id: str
    timestamp: Optional[datetime] = None

class AccessValidateResponse(BaseModel):
    approved: bool
    reason: str
