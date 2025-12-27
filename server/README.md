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

### Session-Based Access Flow

The recommended access flow for face scanning clients:

#### 1. Start Session
```http
POST /api/session/start
Content-Type: application/json

{
  "gate_id": "GATE-MAIN-001"  // Optional: linked to client DEVICE_ID
}
```

#### 2. Scan Faces
```http
POST /api/session/scan
Content-Type: application/json

{
  "session_id": "abc12345",
  "embedding": [0.1, 0.2, ...]
}
```

The server will:
- Add vendors to the session queue
- Validate tasks when PIC is scanned
- Unlock door only if a valid task exists for current time

#### 3. Get Session Status
```http
GET /api/session/{session_id}
```

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

### Access Validation (Legacy)
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
# Database
DB_CONNECTION=sqlite
DB_DATABASE=database.sqlite

# Door Lock IoT
IOT_URL=http://192.168.1.102
IOT_SECRET=sentinel-iot-secret
DOOR_UNLOCK_DURATION=10

# Laravel API (for access logging)
LARAVEL_API_URL=http://127.0.0.1:8000
```

## Door Integration

When a session is approved, the server:
1. Validates that an active task exists for the vendor-PIC pair
2. Checks the current time is within the task's time window
3. Verifies the gate is authorized for the task
4. Logs the access event to Laravel API
5. Sends unlock command to the solenoid IoT device
6. Logs exit event when door locks

If any validation fails, access is denied and no unlock command is sent.

