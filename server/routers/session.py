"""
Session Router - Endpoints for access session management
"""
import asyncio
import os
import httpx
from fastapi import APIRouter, Depends, HTTPException, BackgroundTasks
from sqlalchemy.orm import Session
from sqlalchemy import and_
from pydantic import BaseModel
from typing import Optional, List
from datetime import datetime
from dotenv import load_dotenv

import database
import crud
import models
from session_manager import session_manager, SessionState, ScannedPerson
import solenoid_client

load_dotenv(os.path.join(os.path.dirname(__file__), '..', '.env'))

# Laravel API URL for logging access events
LARAVEL_API_URL = os.getenv("LARAVEL_API_URL", "http://127.0.0.1:8000")

router = APIRouter(
    prefix="/api/session",
    tags=["session"],
)


class StartSessionRequest(BaseModel):
    gate_id: Optional[str] = None  # This is the door_id from client DEVICE_ID


class ScanRequest(BaseModel):
    session_id: str
    embedding: List[float]


class SessionResponse(BaseModel):
    session_id: str
    state: str
    message: str
    vendors: list = []
    pic: Optional[dict] = None
    task_id: Optional[int] = None


async def log_access_to_laravel(
    door_id: str,
    event_type: str,
    vendor_id: int = None,
    pic_id: int = None,
    task_id: int = None,
    session_id: str = None,
    reason: str = None,
    details: dict = None
):
    """Log access event to Laravel API"""
    try:
        async with httpx.AsyncClient(timeout=5.0) as client:
            response = await client.post(
                f"{LARAVEL_API_URL}/api/doors/log-access",
                json={
                    "door_id": door_id,
                    "event_type": event_type,
                    "vendor_id": vendor_id,
                    "pic_id": pic_id,
                    "task_id": task_id,
                    "session_id": session_id,
                    "reason": reason,
                    "details": details,
                }
            )
            print(f"[LARAVEL] Logged access: {response.status_code}")
            return response.json()
    except Exception as e:
        print(f"[LARAVEL] Failed to log access: {e}")
        return None


def validate_task_for_access(
    db: Session,
    vendor_id: int,
    pic_id: int,
    door_id: str
) -> tuple[bool, Optional[models.Task], str]:
    """
    Validate if there's an active task that allows the vendor-PIC pair
    to access the gate at the current time.
    
    Returns: (is_valid, task, reason)
    """
    now = datetime.now()
    
    # Find the gate by door_id
    gate = db.query(models.Gate).filter(models.Gate.gate_id == door_id).first()
    if not gate:
        # Try matching by the new door_id field (if existing gate_id doesn't match)
        # For backwards compatibility, we check both
        return False, None, f"Gate not found for door_id: {door_id}"
    
    # Find active task for this vendor-PIC pair using many-to-many relationship
    task = db.query(models.Task).join(
        models.task_vendor,
        models.Task.id == models.task_vendor.c.task_id
    ).filter(
        and_(
            models.task_vendor.c.vendor_id == vendor_id,
            models.Task.pic_id == pic_id,
            models.Task.status == 'active',
            models.Task.start_time <= now,
            models.Task.end_time >= now
        )
    ).first()
    
    if not task:
        return False, None, "No active task found for this vendor-PIC pair at current time"
    
    # Check if gate is allowed in task
    task_gate_ids = [g.gate_id for g in task.gates]
    if gate.gate_id not in task_gate_ids:
        return False, task, f"Gate '{gate.name}' is not authorized for this task"
    
    return True, task, "OK"


def validate_task_for_access_by_pair(
    db: Session,
    vendor_id: int,
    pic_id: int,
    door_id: Optional[str] = None
) -> tuple[bool, Optional[models.Task], str]:
    """
    Validate if there's an active task that allows the vendor-PIC pair
    at the current time. Gate validation is optional (if door_id provided).
    
    This ensures that a valid task MUST exist for access to be granted.
    
    Returns: (is_valid, task, reason)
    """
    now = datetime.now()
    
    # Find active task for this vendor-PIC pair within time window using many-to-many
    task = db.query(models.Task).join(
        models.task_vendor,
        models.Task.id == models.task_vendor.c.task_id
    ).filter(
        and_(
            models.task_vendor.c.vendor_id == vendor_id,
            models.Task.pic_id == pic_id,
            models.Task.status == 'active',
            models.Task.start_time <= now,
            models.Task.end_time >= now
        )
    ).first()
    
    if not task:
        return False, None, "No active task found for this vendor-PIC pair at current time"
    
    # If door_id provided, also validate gate authorization
    if door_id:
        gate = db.query(models.Gate).filter(models.Gate.gate_id == door_id).first()
        if gate:
            task_gate_ids = [g.gate_id for g in task.gates]
            if gate.gate_id not in task_gate_ids:
                return False, task, f"Gate '{gate.name}' is not authorized for this task"
    
    return True, task, "OK"


@router.post("/start", response_model=SessionResponse)
def start_session(request: StartSessionRequest = None):
    """Start a new access session"""
    gate_id = request.gate_id if request else None
    session = session_manager.create_session(gate_id=gate_id)
    
    return SessionResponse(
        session_id=session.id,
        state=session.state,
        message="Session started. Scan vendor faces first.",
        vendors=[],
        pic=None,
    )


@router.post("/scan", response_model=SessionResponse)
async def scan_face(
    request: ScanRequest,
    background_tasks: BackgroundTasks,
    db: Session = Depends(database.get_db)
):
    """
    Process a face scan within a session.
    - If vendor detected: add to queue
    - If PIC detected: validate task, approve session and unlock door
    """
    session = session_manager.get_session(request.session_id)
    if not session:
        raise HTTPException(status_code=404, detail="Session not found or expired")
    
    if session.state in [SessionState.APPROVED, SessionState.COMPLETED]:
        raise HTTPException(status_code=400, detail="Session already completed")
    
    if session.state == SessionState.EXPIRED:
        raise HTTPException(status_code=400, detail="Session expired")

    # Identify the person
    user, score = crud.identify_user(db, request.embedding, threshold=0.45)
    
    if not user:
        return SessionResponse(
            session_id=session.id,
            state=session.state,
            message=f"Face not recognized (score: {score:.2f}). Please try again.",
            vendors=[v.name for v in session.vendors],
            pic=None,
        )

    person = ScannedPerson(
        user_id=user.id,
        name=user.name,
        role=user.role,
    )

    # Handle based on role
    if user.role == "vendor":
        # Check if this vendor is already in the session
        already_scanned = any(v.user_id == user.id for v in session.vendors)
        
        if already_scanned:
            # Vendor already scanned - remind to scan PIC
            return SessionResponse(
                session_id=session.id,
                state=SessionState.WAITING_PIC,
                message=f"Vendor '{user.name}' already scanned. Now scan PIC to approve.",
                vendors=[v.name for v in session.vendors],
                pic=None,
            )
        
        session_manager.add_vendor(request.session_id, person)
        return SessionResponse(
            session_id=session.id,
            state=SessionState.WAITING_PIC,  # After vendor, we're waiting for PIC
            message=f"Vendor '{user.name}' registered. Now scan PIC to approve (or add more vendors).",
            vendors=[v.name for v in session.vendors],
            pic=None,
        )

    elif user.role in ["dcfm", "soc"]:
        # PIC/Admin detected - validate task before unlock
        if len(session.vendors) == 0:
            return SessionResponse(
                session_id=session.id,
                state=session.state,
                message="No vendors scanned yet. Vendors must scan first.",
                vendors=[],
                pic=None,
            )

        # ALWAYS validate task for vendor-PIC pair
        # Task validation is MANDATORY - access should be denied if no valid task exists
        door_id = session.gate_id
        validated_task = None
        deny_reason = None
        
        # Check if there's a valid task for any vendor with this PIC
        for vendor in session.vendors:
            is_valid, task, reason = validate_task_for_access_by_pair(
                db, vendor.user_id, user.id, door_id
            )
            if is_valid:
                validated_task = task
                break
            else:
                deny_reason = reason  # Keep the last reason for error message
        
        # If no valid task found, DENY access
        if not validated_task:
            # Build a clearer error message
            vendor_names = ", ".join([v.name for v in session.vendors])
            error_msg = f"'{user.name}' is not assigned as PIC for vendor(s): {vendor_names}"
            
            # Log denied access
            if door_id:
                background_tasks.add_task(
                    log_access_to_laravel,
                    door_id=door_id,
                    event_type="denied",
                    vendor_id=session.vendors[0].user_id if session.vendors else None,
                    pic_id=user.id,
                    session_id=session.id,
                    reason=error_msg,
                    details={"vendors": [v.name for v in session.vendors], "pic_attempted": user.name}
                )
            
            return SessionResponse(
                session_id=session.id,
                state=SessionState.WAITING_PIC,  # Stay in waiting_pic state so correct PIC can scan
                message=f"No task found. {error_msg}",
                vendors=[v.name for v in session.vendors],
                pic=None,
            )

        session_manager.set_pic(request.session_id, person)
        
        # Store task_id in session for logging
        task_id = validated_task.id if validated_task else None
        
        # Trigger door unlock in background
        background_tasks.add_task(
            unlock_door_flow, 
            session_id=request.session_id,
            door_id=door_id,
            task_id=task_id,
            vendor_ids=[v.user_id for v in session.vendors],
            pic_id=user.id
        )

        return SessionResponse(
            session_id=session.id,
            state=SessionState.APPROVED,
            message=f"Access APPROVED by PIC '{user.name}'. Door unlocking...",
            vendors=[v.name for v in session.vendors],
            pic={"name": user.name, "user_id": user.id},
            task_id=task_id,
        )

    else:
        return SessionResponse(
            session_id=session.id,
            state=session.state,
            message=f"Unknown role '{user.role}'. Cannot process.",
            vendors=[v.name for v in session.vendors],
            pic=None,
        )


async def unlock_door_flow(
    session_id: str,
    door_id: str = None,
    task_id: int = None,
    vendor_ids: List[int] = None,
    pic_id: int = None
):
    """Background task to unlock door, wait, then lock"""
    print(f"[SESSION {session_id}] Triggering door unlock sequence...")
    
    # Log entry event to Laravel
    if door_id:
        await log_access_to_laravel(
            door_id=door_id,
            event_type="entry",
            vendor_id=vendor_ids[0] if vendor_ids else None,
            pic_id=pic_id,
            task_id=task_id,
            session_id=session_id,
            details={"all_vendor_ids": vendor_ids}
        )
    
    result = await solenoid_client.unlock_and_auto_lock()
    
    # Log exit event after door locks
    if door_id:
        await log_access_to_laravel(
            door_id=door_id,
            event_type="exit",
            vendor_id=vendor_ids[0] if vendor_ids else None,
            pic_id=pic_id,
            task_id=task_id,
            session_id=session_id,
        )
    
    print(f"[SESSION {session_id}] Door sequence complete: {result}")
    session_manager.complete_session(session_id)


@router.get("/{session_id}", response_model=SessionResponse)
def get_session_status(session_id: str):
    """Get current session status"""
    session = session_manager.get_session(session_id)
    if not session:
        raise HTTPException(status_code=404, detail="Session not found")

    state_messages = {
        SessionState.WAITING_VENDORS: "Waiting for vendors to scan.",
        SessionState.WAITING_PIC: "Vendors registered. Waiting for PIC.",
        SessionState.APPROVED: "Access approved. Door unlocking.",
        SessionState.COMPLETED: "Session completed.",
        SessionState.EXPIRED: "Session expired.",
        SessionState.CANCELLED: "Session cancelled.",
    }

    return SessionResponse(
        session_id=session.id,
        state=session.state,
        message=state_messages.get(session.state, "Unknown state"),
        vendors=[v.name for v in session.vendors],
        pic={"name": session.pic.name, "user_id": session.pic.user_id} if session.pic else None,
    )


@router.delete("/{session_id}")
def cancel_session(session_id: str):
    """Cancel an active session"""
    session = session_manager.get_session(session_id)
    if not session:
        raise HTTPException(status_code=404, detail="Session not found")

    session_manager.cancel_session(session_id)
    return {"message": "Session cancelled", "session_id": session_id}
