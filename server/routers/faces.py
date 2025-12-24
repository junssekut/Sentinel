from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session
import schemas, crud, database

router = APIRouter(
    prefix="/api/faces",
    tags=["faces"],
    responses={404: {"description": "Not found"}},
)

@router.post("/enroll")
def enroll_face(user: schemas.UserCreate, db: Session = Depends(database.get_db)):
    """
    Enroll a new user with face data.
    """
    # Check if face_id exists
    if user.face_id and crud.get_user_by_face_id(db, user.face_id):
         raise HTTPException(status_code=400, detail="Face ID already registered")
         
    return crud.create_user(db=db, user=user)

@router.post("/identify")
def identify_face(request: schemas.IdentifyRequest, db: Session = Depends(database.get_db)):
    """
    Identify a user from an embedding (1:N search).
    """
    user, score = crud.identify_user(db, request.embedding)
    if user:
        return {
            "match": True,
            "user_id": user.id,
            "face_id": user.face_id,
            "name": user.name,
            "role": user.role,
            "score": float(score)
        }
    return {
        "match": False,
        "score": float(score)
    }
