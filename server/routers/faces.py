from fastapi import APIRouter, Depends, HTTPException, BackgroundTasks
from sqlalchemy.orm import Session
import schemas, crud, database, models
import logging

logger = logging.getLogger(__name__)

router = APIRouter(
    prefix="/api/faces",
    tags=["faces"],
    responses={404: {"description": "Not found"}},
)

@router.post("/enroll")
def enroll_face(user: schemas.UserCreate, db: Session = Depends(database.get_db)):
    """
    Enroll face data for an EXISTING user.
    The user must already exist in the database (created via web dashboard).
    This endpoint only updates their face_image and face_embedding.
    """
    # Find existing user by name
    existing_user = db.query(models.User).filter(models.User.name == user.name).first()
    
    if not existing_user:
        raise HTTPException(
            status_code=404, 
            detail=f"User '{user.name}' not found. Please create the user in the web dashboard first."
        )
    
    # Check if user already has face data
    if existing_user.face_image and existing_user.face_embedding:
        raise HTTPException(
            status_code=400, 
            detail=f"User '{user.name}' already has face data enrolled."
        )
    
    # Update the existing user with face data
    if user.face_image:
        existing_user.face_image = user.face_image
    if user.embedding:
        existing_user.face_embedding = user.embedding
    
    db.commit()
    db.refresh(existing_user)
    
    return {
        "status": "success",
        "message": f"Face enrolled for user '{existing_user.name}'",
        "user_id": existing_user.id,
        "name": existing_user.name,
        "role": existing_user.role
    }

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
            "name": user.name,
            "role": user.role,
            "score": float(score)
        }
    return {
        "match": False,
        "score": float(score)
    }


def process_embedding_task(user_id: int, user_name: str, face_image: str):
    """
    Background task to generate embedding from face_image and update user.
    """
    # Create a new database session for the background task
    db = database.SessionLocal()
    try:
        logger.info(f"Starting background embedding generation for user {user_id} ({user_name})")
        
        # Generate embedding from face_image
        embedding = crud.get_embedding_from_b64(face_image)
        
        if embedding is None:
            logger.error(f"Failed to generate embedding for user {user_id} ({user_name})")
            return
        
        # Update user with the generated embedding
        user = db.query(models.User).filter(models.User.id == user_id).first()
        if user:
            user.face_embedding = embedding.tolist()
            db.commit()
            logger.info(f"Successfully generated and saved embedding for user {user_id} ({user_name})")
        else:
            logger.error(f"User {user_id} not found when trying to save embedding")
            
    except Exception as e:
        logger.error(f"Error generating embedding for user {user_id}: {e}")
        db.rollback()
    finally:
        db.close()


@router.post("/enroll-from-image")
def enroll_from_image(
    user: schemas.UserCreate, 
    background_tasks: BackgroundTasks,
    db: Session = Depends(database.get_db),
    sync: bool = False  # If True, generate embedding synchronously
):
    """
    Enroll face data for an EXISTING user by generating embedding from face_image.
    This is called from the web dashboard when approving a user.
    
    If sync=True, embedding is generated immediately (blocking).
    If sync=False (default), runs as background task (non-blocking).
    """
    # Find existing user by name
    existing_user = db.query(models.User).filter(models.User.name == user.name).first()
    
    if not existing_user:
        raise HTTPException(
            status_code=404, 
            detail=f"User '{user.name}' not found."
        )
    
    # Require face_image
    if not user.face_image:
        raise HTTPException(
            status_code=400, 
            detail="face_image is required."
        )
    
    # Update face_image immediately
    existing_user.face_image = user.face_image
    db.commit()
    
    if sync:
        # Synchronous mode: generate embedding immediately
        logger.info(f"Generating embedding synchronously for user {existing_user.id} ({existing_user.name})")
        embedding = crud.get_embedding_from_b64(user.face_image)
        
        if embedding is not None:
            existing_user.face_embedding = embedding.tolist()
            db.commit()
            logger.info(f"Successfully saved embedding for user {existing_user.id}")
            return {
                "status": "success",
                "message": f"Face enrolled and embedding generated for user '{existing_user.name}'",
                "user_id": existing_user.id,
                "name": existing_user.name,
                "role": existing_user.role
            }
        else:
            logger.error(f"Failed to generate embedding for user {existing_user.id}")
            return {
                "status": "failed",
                "message": f"Failed to generate embedding for user '{existing_user.name}'",
                "user_id": existing_user.id,
                "name": existing_user.name,
                "role": existing_user.role
            }
    else:
        # Async mode: background task (non-blocking)
        background_tasks.add_task(
            process_embedding_task,
            existing_user.id,
            existing_user.name,
            user.face_image
        )
        
        return {
            "status": "processing",
            "message": f"Face image saved. Embedding generation started in background for user '{existing_user.name}'",
            "user_id": existing_user.id,
            "name": existing_user.name,
            "role": existing_user.role
        }


@router.get("/status/{user_id}")
def get_enrollment_status(user_id: int, db: Session = Depends(database.get_db)):
    """
    Check the enrollment status of a user.
    Returns whether the user has face_image and face_embedding.
    """
    user = db.query(models.User).filter(models.User.id == user_id).first()
    
    if not user:
        raise HTTPException(status_code=404, detail="User not found")
    
    has_image = bool(user.face_image)
    has_embedding = bool(user.face_embedding)
    
    if has_embedding:
        status = "complete"
    elif has_image:
        status = "processing"
    else:
        status = "not_enrolled"
    
    return {
        "user_id": user.id,
        "name": user.name,
        "status": status,
        "has_face_image": has_image,
        "has_face_embedding": has_embedding
    }
