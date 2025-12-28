from sqlalchemy.orm import Session
from datetime import datetime
import models, schemas
import json
import base64
import numpy as np
import cv2
try:
    import insightface
    from insightface.app import FaceAnalysis
    FACE_APP = None  # Lazy load
except ImportError:
    insightface = None
    FACE_APP = None

from embedding_utils import cosine_similarity
import io
from PIL import Image

SIMILARITY_THRESHOLD = 0.40  # InsightFace typically uses lower threshold (0.3-0.5)

def get_face_app():
    """Lazy load the face analysis app"""
    global FACE_APP
    if FACE_APP is None and insightface is not None:
        FACE_APP = FaceAnalysis(providers=['CPUExecutionProvider'])
        FACE_APP.prepare(ctx_id=0, det_size=(640, 640))
    return FACE_APP

def get_embedding_from_b64(b64_str: str):
    """
    Decodes a base64 image string and generates face embedding using InsightFace.
    """
    if insightface is None:
        print("Warning: insightface library not installed. Skipping embedding generation.")
        return None

    try:
        # Handle data URI scheme if present
        if ',' in b64_str:
            b64_str = b64_str.split(',')[1]
            
        image_data = base64.b64decode(b64_str)
        image = Image.open(io.BytesIO(image_data))
        image = image.convert('RGB')
        image_np = np.array(image)
        
        # Convert RGB to BGR for OpenCV/InsightFace
        image_bgr = cv2.cvtColor(image_np, cv2.COLOR_RGB2BGR)
        
        # Get face app
        app = get_face_app()
        if app is None:
            return None
            
        # Detect faces and get embeddings
        faces = app.get(image_bgr)
        if len(faces) > 0:
            # Return the embedding of the first detected face
            return faces[0].embedding
        return None
    except Exception as e:
        print(f"Error generating embedding: {e}")
        return None

def verify_identity(user: models.User, incoming_embedding: list[float]) -> tuple[bool, float]:
    """
    Verifies if the incoming embedding matches the user's stored face image.
    """
    if not user.face_image or not incoming_embedding:
        return True, 1.0  # Skip if no data (fallback to ID trust)

    # 1. Generate embedding from stored DB image
    stored_embedding = get_embedding_from_b64(user.face_image)
    
    if stored_embedding is None:
        if insightface is None:
            # Bypass verification if library is missing
            return True, 1.0
        return False, 0.0 # stored image invalid

    # 2. Compare using Cosine Similarity
    incoming_vec = np.array(incoming_embedding)
    score = cosine_similarity(stored_embedding, incoming_vec)
    
    return score > SIMILARITY_THRESHOLD, score

def log_access(db: Session, vendor: models.User, pic: models.User, gate: models.Gate, task: models.Task, success: bool, reason: str, ip: str, similarity: float = None):
    details = {
        'vendor_id': vendor.id if vendor else None,
        'pic_id': pic.id if pic else None,
        'task_id': task.id if task else None,
        'gate_record_id': gate.id if gate else None,
        'similarity_score': str(similarity) if similarity is not None else None
    }
    
    log = models.AuditLog(
        action='access_validated',
        entity_type='access_request',
        entity_id=None,
        user_id=None, 
        details=details,
        ip_address=ip,
        success=success,
        reason=reason,
        created_at=datetime.now()
    )
    db.add(log)
    db.commit()


def get_user_by_id(db: Session, user_id: int):
    """Get user by primary key ID"""
    return db.query(models.User).filter(models.User.id == user_id).first()


def get_user_by_name(db: Session, name: str):
    """Get user by name"""
    return db.query(models.User).filter(models.User.name == name).first()


def identify_user(db: Session, embedding: list[float], threshold: float = 0.45):
    """
    Iterates through all users with stored embeddings and finds the best match.
    Note: For large scale, use a vector DB (Milvus/Faiss). This is O(N).
    """
    if not embedding:
        return None, 0.0

    users = db.query(models.User).filter(models.User.face_embedding.isnot(None)).all()
    best_match = None
    best_score = -1.0

    target_embedding = np.array(embedding)

    for user in users:
        stored_emb = user.face_embedding
        if stored_emb is None:
            continue

        stored_emb_np = np.array(stored_emb)
        score = cosine_similarity(stored_emb_np, target_embedding)
        if score > best_score:
            best_score = score
            best_match = user
            
    if best_score > threshold:
        return best_match, best_score
        
    return None, best_score


def validate_access(db: Session, request: schemas.AccessValidateRequest, ip_address: str):
    """
    Validate access based on vendor_id and pic_id (user IDs).
    """
    # Step 1: Verify vendor exists
    vendor = get_user_by_id(db, request.vendor_id)
    if not vendor:
        return {"approved": False, "reason": "Vendor not found", "similarity": 0.0}

    # Verify Vendor Identity (Embedding Match)
    if request.vendor_embedding:
        is_match, score = verify_identity(vendor, request.vendor_embedding)
        if not is_match:
            log_access(db, vendor, None, None, None, False, f"Vendor face mismatch (Score: {score:.2f})", ip_address, score)
            return {"approved": False, "reason": "Vendor face verification failed", "similarity": score}

    # Step 2: Verify vendor role
    if vendor.role != 'vendor':
        log_access(db, vendor, None, None, None, False, "Invalid vendor role", ip_address)
        return {"approved": False, "reason": "Invalid vendor role"}

    # Step 3: Verify PIC exists
    pic = get_user_by_id(db, request.pic_id)
    if not pic:
        log_access(db, vendor, None, None, None, False, "PIC not found", ip_address)
        return {"approved": False, "reason": "PIC not found"}

    # Verify PIC Identity (Embedding Match)
    if request.pic_embedding:
        is_match, score = verify_identity(pic, request.pic_embedding)
        if not is_match:
            log_access(db, vendor, pic, None, None, False, f"PIC face mismatch (Score: {score:.2f})", ip_address, score)
            return {"approved": False, "reason": "PIC face verification failed", "similarity": score}

    # Step 4: Verify PIC is NOT a vendor
    if pic.role == 'vendor':
        log_access(db, vendor, pic, None, None, False, "Invalid PIC role", ip_address)
        return {"approved": False, "reason": "Invalid PIC role"}

    # Step 5: Verify Gate exists
    gate = db.query(models.Gate).filter(models.Gate.gate_id == request.gate_id).first()
    if not gate:
        log_access(db, vendor, pic, None, None, False, "Gate not found", ip_address)
        return {"approved": False, "reason": "Gate not found"}

    # Step 6: Verify Gate active
    if not gate.is_active:
        log_access(db, vendor, pic, gate, None, False, "Gate is inactive", ip_address)
        return {"approved": False, "reason": "Gate is inactive"}

    # Step 7: Find Active Task where vendor is assigned and PIC matches
    task = db.query(models.Task).filter(
        models.Task.vendors.any(id=vendor.id),  # Check if vendor is in the vendors list
        models.Task.pic_id == pic.id,
        models.Task.status == 'active'
    ).first()

    if not task:
        log_access(db, vendor, pic, gate, None, False, "No active task found", ip_address)
        return {"approved": False, "reason": "No active task found for this vendor-PIC pair"}


    # Step 8: Verify Time Window
    now = datetime.now()
    if not (task.start_time <= now <= task.end_time):
        log_access(db, vendor, pic, gate, task, False, "Task outside time window", ip_address)
        return {"approved": False, "reason": "Task is outside valid time window"}

    # Step 9: Verify Gate Authorization
    if gate not in task.gates:
        log_access(db, vendor, pic, gate, task, False, "Gate not authorized", ip_address)
        return {"approved": False, "reason": "Gate not authorized for this task"}

    # Success!
    log_access(db, vendor, pic, gate, task, True, "OK", ip_address)
    return {"approved": True, "reason": "OK"}


def update_gate_heartbeat(db: Session, device_id: str) -> dict:
    """
    Update a gate's heartbeat timestamp and integration status.
    Called by client devices to indicate they are online.
    """
    from zoneinfo import ZoneInfo
    
    gate = db.query(models.Gate).filter(models.Gate.door_id == device_id).first()
    
    if not gate:
        return {"success": False, "error": f"Gate not found for device_id: {device_id}"}
    
    # Use Asia/Jakarta timezone to match Laravel app
    jakarta_tz = ZoneInfo("Asia/Jakarta")
    gate.last_heartbeat_at = datetime.now(jakarta_tz)
    gate.integration_status = "integrated"
    db.commit()
    
    return {"success": True, "gate_id": gate.id, "gate_name": gate.name}
