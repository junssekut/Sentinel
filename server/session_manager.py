"""
Session Manager for Access Control
Manages in-memory sessions for vendor→PIC sequential access flow.
"""
import uuid
import asyncio
from datetime import datetime, timedelta
from typing import Dict, List, Optional
from enum import Enum
from dataclasses import dataclass, field


class SessionState(str, Enum):
    WAITING_VENDORS = "waiting_vendors"  # Waiting for vendors to scan
    WAITING_PIC = "waiting_pic"          # At least 1 vendor scanned, waiting for PIC
    APPROVED = "approved"                 # PIC approved, door unlocking
    COMPLETED = "completed"               # Session finished
    EXPIRED = "expired"                   # Session timed out
    CANCELLED = "cancelled"               # Session cancelled


@dataclass
class ScannedPerson:
    user_id: int
    name: str
    role: str
    scanned_at: datetime = field(default_factory=datetime.now)


@dataclass
class AccessSession:
    id: str
    state: SessionState
    created_at: datetime
    expires_at: datetime
    vendors: List[ScannedPerson] = field(default_factory=list)
    pic: Optional[ScannedPerson] = None
    gate_id: Optional[str] = None

    def is_expired(self) -> bool:
        return datetime.now() > self.expires_at

    def to_dict(self) -> dict:
        return {
            "session_id": self.id,
            "state": self.state,
            "vendors": [
                {"name": v.name, "user_id": v.user_id, "role": v.role}
                for v in self.vendors
            ],
            "pic": {"name": self.pic.name, "user_id": self.pic.user_id} if self.pic else None,
            "created_at": self.created_at.isoformat(),
            "expires_at": self.expires_at.isoformat(),
        }


class SessionManager:
    """In-memory session store"""

    def __init__(self, session_timeout_minutes: int = 5):
        self.sessions: Dict[str, AccessSession] = {}
        self.session_timeout = timedelta(minutes=session_timeout_minutes)

    def create_session(self, gate_id: Optional[str] = None) -> AccessSession:
        """Create a new access session"""
        session_id = str(uuid.uuid4())[:8]
        now = datetime.now()
        session = AccessSession(
            id=session_id,
            state=SessionState.WAITING_VENDORS,
            created_at=now,
            expires_at=now + self.session_timeout,
            gate_id=gate_id,
        )
        self.sessions[session_id] = session
        return session

    def get_session(self, session_id: str) -> Optional[AccessSession]:
        """Get session by ID, checking expiration"""
        session = self.sessions.get(session_id)
        if session and session.is_expired():
            session.state = SessionState.EXPIRED
        return session

    def add_vendor(self, session_id: str, person: ScannedPerson) -> bool:
        """Add a vendor to the session queue"""
        session = self.get_session(session_id)
        if not session:
            return False
        if session.state not in [SessionState.WAITING_VENDORS, SessionState.WAITING_PIC]:
            return False

        # Check if already scanned (by user_id)
        for v in session.vendors:
            if v.user_id == person.user_id:
                return True  # Already in queue

        session.vendors.append(person)
        session.state = SessionState.WAITING_PIC  # Now waiting for PIC
        return True

    def set_pic(self, session_id: str, person: ScannedPerson) -> bool:
        """Set the PIC and approve the session"""
        session = self.get_session(session_id)
        if not session:
            return False
        if session.state != SessionState.WAITING_PIC:
            return False
        if len(session.vendors) == 0:
            return False  # Need at least 1 vendor

        session.pic = person
        session.state = SessionState.APPROVED
        return True

    def complete_session(self, session_id: str):
        """Mark session as completed"""
        session = self.get_session(session_id)
        if session:
            session.state = SessionState.COMPLETED

    def cancel_session(self, session_id: str):
        """Cancel a session"""
        session = self.get_session(session_id)
        if session:
            session.state = SessionState.CANCELLED

    def cleanup_expired(self):
        """Remove expired sessions"""
        expired = [
            sid for sid, s in self.sessions.items()
            if s.is_expired() or s.state in [SessionState.COMPLETED, SessionState.CANCELLED]
        ]
        for sid in expired:
            del self.sessions[sid]


# Global session manager instance
session_manager = SessionManager()
