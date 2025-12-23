from sqlalchemy.orm import Session
from datetime import datetime
import models, schemas
import json

def log_access(db: Session, vendor: models.User, pic: models.User, gate: models.Gate, task: models.Task, success: bool, reason: str, ip: str):
    details = {
        'vendor_id': vendor.id if vendor else None,
        'pic_id': pic.id if pic else None,
        'task_id': task.id if task else None,
        'gate_record_id': gate.id if gate else None
    }
    
    log = models.AuditLog(
        action='access_validated',
        entity_type='access_request',
        entity_id=None,
        user_id=None, # System action essentially
        details=details, # SQLAlchemy handles dict to JSON if using a specific type or we dump it
        ip_address=ip,
        success=success,
        reason=reason,
        created_at=datetime.now()
    )
    db.add(log)
    db.commit()

def validate_access(db: Session, request: schemas.AccessValidateRequest, ip_address: str):
    # Step 1: Verify vendor exists
    vendor = db.query(models.User).filter(models.User.face_id == request.vendor_face_id).first()
    if not vendor:
        return {"approved": False, "reason": "Vendor not found"}

    # Step 2: Verify vendor role
    if vendor.role != 'vendor':
        log_access(db, vendor, None, None, None, False, "Invalid vendor role", ip_address)
        return {"approved": False, "reason": "Invalid vendor role"}

    # Step 3: Verify PIC exists
    pic = db.query(models.User).filter(models.User.face_id == request.pic_face_id).first()
    if not pic:
        log_access(db, vendor, None, None, None, False, "PIC not found", ip_address)
        return {"approved": False, "reason": "PIC not found"}

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

    # Step 7: Find Active Task
    # Must be status='active', matches vendor_id, pic_id
    task = db.query(models.Task).filter(
        models.Task.vendor_id == vendor.id,
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
    # Check if this task is associated with this gate
    if gate not in task.gates:
        log_access(db, vendor, pic, gate, task, False, "Gate not authorized", ip_address)
        return {"approved": False, "reason": "Gate not authorized for this task"}

    # Success!
    log_access(db, vendor, pic, gate, task, True, "OK", ip_address)
    return {"approved": True, "reason": "OK"}
