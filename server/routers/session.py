"""
Session Router - Endpoints for access session management
"""
import asyncio
from fastapi import APIRouter, Depends, HTTPException, BackgroundTasks
from sqlalchemy.orm import Session
from pydantic import BaseModel
from typing import Optional, List

import database
import crud
from session_manager import session_manager, SessionState, ScannedPerson
import solenoid_client

router = APIRouter(
    prefix="/api/session",
    tags=["session"],
)


class StartSessionRequest(BaseModel):
    gate_id: Optional[str] = None


class ScanRequest(BaseModel):
    session_id: str
    embedding: List[float]


class SessionResponse(BaseModel):
    session_id: str
    state: str
    message: str
    vendors: list = []
    pic: Optional[dict] = None


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
    - If PIC detected: approve session and unlock door
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
        face_id=user.face_id,
        name=user.name,
        role=user.role,
    )

    # Handle based on role
    if user.role == "vendor":
        session_manager.add_vendor(request.session_id, person)
        return SessionResponse(
            session_id=session.id,
            state=session.state,
            message=f"Vendor '{user.name}' registered. Waiting for more vendors or PIC.",
            vendors=[v.name for v in session.vendors],
            pic=None,
        )

    elif user.role in ["pic", "dcfm", "soc"]:
        # PIC/Admin detected - validate and unlock
        if len(session.vendors) == 0:
            return SessionResponse(
                session_id=session.id,
                state=session.state,
                message="No vendors scanned yet. Vendors must scan first.",
                vendors=[],
                pic=None,
            )

        session_manager.set_pic(request.session_id, person)
        
        # Trigger door unlock in background
        background_tasks.add_task(unlock_door_flow, request.session_id)

        return SessionResponse(
            session_id=session.id,
            state=SessionState.APPROVED,
            message=f"Access APPROVED by PIC '{user.name}'. Door unlocking...",
            vendors=[v.name for v in session.vendors],
            pic={"name": user.name, "face_id": user.face_id},
        )

    else:
        return SessionResponse(
            session_id=session.id,
            state=session.state,
            message=f"Unknown role '{user.role}'. Cannot process.",
            vendors=[v.name for v in session.vendors],
            pic=None,
        )


async def unlock_door_flow(session_id: str):
    """Background task to unlock door, wait, then lock"""
    print(f"[SESSION {session_id}] Triggering door unlock sequence...")
    
    result = await solenoid_client.unlock_and_auto_lock()
    
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
        pic={"name": session.pic.name, "face_id": session.pic.face_id} if session.pic else None,
    )


@router.delete("/{session_id}")
def cancel_session(session_id: str):
    """Cancel an active session"""
    session = session_manager.get_session(session_id)
    if not session:
        raise HTTPException(status_code=404, detail="Session not found")

    session_manager.cancel_session(session_id)
    return {"message": "Session cancelled", "session_id": session_id}
