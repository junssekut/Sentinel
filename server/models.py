from sqlalchemy import Column, Integer, String, Boolean, DateTime, ForeignKey, Text, JSON, Table
from sqlalchemy.orm import relationship
from database import Base
from datetime import datetime

# Association Table for Gate-Task Many-to-Many
gate_task = Table('gate_task', Base.metadata,
    Column('gate_id', Integer, ForeignKey('gates.id')),
    Column('task_id', Integer, ForeignKey('tasks.id'))
)

class User(Base):
    __tablename__ = "users"

    id = Column(Integer, primary_key=True, index=True)
    name = Column(String)
    email = Column(String, unique=True, index=True)
    role = Column(String) # vendor, dcfm, soc
    face_id = Column(String, unique=True, nullable=True)
    face_image = Column(Text, nullable=True)
    face_embedding = Column(JSON, nullable=True) # Store embedding as JSON list
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    def is_vendor(self):
        return self.role == 'vendor'

    def is_active_vendor(self):
        # Assuming there is no explicit 'is_active' column yet for users, but logic might be added
        # For now, just check role
        return self.role == 'vendor'

class Gate(Base):
    __tablename__ = "gates"

    id = Column(Integer, primary_key=True, index=True)
    name = Column(String)
    gate_id = Column(String, unique=True, index=True) # The IoT ID
    is_active = Column(Boolean, default=True)
    
    tasks = relationship("Task", secondary=gate_task, back_populates="gates")

class Task(Base):
    __tablename__ = "tasks"

    id = Column(Integer, primary_key=True, index=True)
    vendor_id = Column(Integer, ForeignKey("users.id"))
    pic_id = Column(Integer, ForeignKey("users.id"))
    start_time = Column(DateTime)
    end_time = Column(DateTime)
    status = Column(String) # active, completed, revoked

    vendor = relationship("User", foreign_keys=[vendor_id])
    pic = relationship("User", foreign_keys=[pic_id])
    gates = relationship("Gate", secondary=gate_task, back_populates="tasks")

    def is_active(self):
        return self.status == 'active'

    def is_currently_valid(self):
        now = datetime.now()
        return self.start_time <= now <= self.end_time

class AuditLog(Base):
    __tablename__ = "audit_logs"

    id = Column(Integer, primary_key=True, index=True)
    action = Column(String)
    entity_type = Column(String)
    entity_id = Column(Integer, nullable=True)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=True)
    details = Column(JSON, nullable=True)
    ip_address = Column(String, nullable=True)
    success = Column(Boolean, default=True)
    reason = Column(Text, nullable=True)
    created_at = Column(DateTime, default=datetime.now)
    updated_at = Column(DateTime, default=datetime.now, onupdate=datetime.now)
