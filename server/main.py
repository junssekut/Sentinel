# mitigate OpenMP DLL clashes on Windows
import os
os.environ.setdefault("KMP_DUPLICATE_LIB_OK", "TRUE")
os.environ.setdefault("OMP_NUM_THREADS", "1")

from fastapi import FastAPI, Depends, HTTPException, Request
from sqlalchemy.orm import Session
import database, schemas, crud

# Create database tables (not strictly necessary as we use existing DB, but safe for dev)
# models.Base.metadata.create_all(bind=database.engine)

app = FastAPI(
    title="Sentinel Access Control API",
    description="Python FastAPI backend for IoT Access Control integration",
    version="1.0.0"
)

from routers import faces, session
app.include_router(faces.router)
app.include_router(session.router)

@app.get("/")
def read_root():
    return {"message": "Sentinel Access Control API is running"}

@app.post("/api/access/validate", response_model=schemas.AccessValidateResponse)
def validate_access(request: schemas.AccessValidateRequest, http_request: Request, db: Session = Depends(database.get_db)):
    """
    Validate access request from IoT device.
    """
    client_ip = http_request.client.host
    result = crud.validate_access(db, request, client_ip)
    
    if not result['approved'] and result['reason'] in ["Vendor not found", "PIC not found", "Gate not found"]:
        # Match typical 403 or 404 behavior, but PHP returned 403 for denial usually
        # The PHP code returned 403 if !approved.
        pass
    
    # Return JSON response. If denial, we still return the structure but maybe set status code?
    # The PHP code: return response()->json($result, $result['approved'] ? 200 : 403);
    
    from fastapi.responses import JSONResponse
    return JSONResponse(
        content=result,
        status_code=200 if result['approved'] else 403
    )

@app.post("/api/heartbeat")
def heartbeat(request: schemas.HeartbeatRequest, db: Session = Depends(database.get_db)):
    """
    Receive heartbeat from client device and update gate status directly.
    """
    result = crud.update_gate_heartbeat(db, request.device_id)
    
    if not result.get("success"):
        raise HTTPException(status_code=404, detail=result.get("error"))
    
    return {"success": True, "message": f"Heartbeat OK for {request.device_id}", "gate_name": result.get("gate_name")}
