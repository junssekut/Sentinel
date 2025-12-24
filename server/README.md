# Sentinel API Server

FastAPI backend for IoT Access Control integration.

## Requirements

- Python 3.11+ (3.14 may have compatibility issues with onnxruntime)
- SQLite (default)

## Installation

```bash
# Create virtual environment
python3.11 -m venv venv
source venv/bin/activate

# Install dependencies
pip install -r requirements.txt

# Initialize database
python -c "import database, models; models.Base.metadata.create_all(bind=database.engine)"

# Start server
uvicorn main:app --reload --port 8002
```

## API Endpoints

### Face Enrollment
```http
POST /api/faces/enroll
Content-Type: application/json

{
  "name": "John Doe",
  "role": "vendor",
  "face_image": "<base64>",
  "embedding": [0.1, 0.2, ...]
}
```

### Face Identification
```http
POST /api/faces/identify
Content-Type: application/json

{
  "embedding": [0.1, 0.2, ...]
}
```

### Access Validation
```http
POST /api/access/validate
Content-Type: application/json

{
  "vendor_face_id": "VENDOR-UUID",
  "pic_face_id": "PIC-UUID",
  "gate_id": "GATE-MAIN-001"
}
```

## Configuration

Copy `.env.example` to `.env` and configure:

```env
DATABASE_URL=sqlite:///./sentinel.db
```
