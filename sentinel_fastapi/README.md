# Sentinel Access Control API (Python FastAPI)

This is a Python FastAPI implementation of the Sentinel Access Control API, designed to run as a separate service for IoT integration with **face recognition** capabilities using InsightFace.

## Features

- 🔐 **9-Step Access Validation**: Vendor role, PIC role, active task, time window, gate authorization, and more
- 👤 **Face Recognition**: Uses InsightFace for accurate face verification via embedding comparison
- 📊 **Automatic Documentation**: Interactive API docs with Swagger UI
- 🗄️ **Database Integration**: Supports both SQLite and MySQL
- 📝 **Audit Logging**: Complete access attempt logging for security audits

## Prerequisites

- Python 3.9+ (tested on Python 3.14)
- MySQL or SQLite Database (Sentinel DB)
- Existing Sentinel Laravel application (for database schema)

## Installation

### 1. Create a virtual environment (recommended)
```bash
cd sentinel_fastapi
python -m venv venv
source venv/bin/activate  # On Windows: venv\Scripts\activate
```

### 2. Install dependencies
```bash
pip install -r requirements.txt
```

**Note**: InsightFace will download face recognition models (~100MB) on first use. This is automatic.

### 3. Environment Variables
The application automatically loads the `.env` file from the parent directory (`../.env`). Ensure your database credentials are correct.

**Example `.env`:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sentinel
DB_USERNAME=root
DB_PASSWORD=your_password
```

For SQLite:
```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

## Running the Server

### Development Server
```bash
uvicorn main:app --reload --port 8002
```

### Production Server
```bash
uvicorn main:app --host 0.0.0.0 --port 8002 --workers 4
```

The API will be available at:
- **API Root**: http://127.0.0.1:8002
- **Validation Endpoint**: `POST /api/access/validate`
- **Interactive Documentation**: http://127.0.0.1:8002/docs
- **Alternative Docs**: http://127.0.0.1:8002/redoc

## API Endpoints

### 🏠 GET `/`
Health check endpoint.

**Response:**
```json
{
  "message": "Sentinel Access Control API is running"
}
```

---

### 🔓 POST `/api/access/validate`

Validates an access request from an IoT device with face recognition.

#### Request Body

```json
{
  "vendor_face_id": "string",
  "pic_face_id": "string",
  "gate_id": "string",
  "timestamp": "2023-01-01T12:00:00",  // Optional
  "vendor_embedding": [0.123, 0.456, ...],  // Optional: 512-d vector from InsightFace
  "pic_embedding": [0.789, 0.012, ...]      // Optional: 512-d vector from InsightFace
}
```

**Field Descriptions:**
- `vendor_face_id`: The unique face ID of the vendor (stored in database)
- `pic_face_id`: The unique face ID of the PIC (Person In Charge)
- `gate_id`: The gate identifier where access is requested
- `timestamp`: Optional ISO-8601 timestamp of the request
- `vendor_embedding`: **Optional** 512-dimensional face embedding vector from InsightFace. If provided, it will be compared against the vendor's stored face image for verification.
- `pic_embedding`: **Optional** 512-dimensional face embedding vector from InsightFace. If provided, it will be compared against the PIC's stored face image for verification.

#### Validation Process

The API performs **9 comprehensive validation steps**:

1. ✅ **Vendor Exists**: Verify vendor is in the database
2. ✅ **Vendor Face Match** *(if embedding provided)*: Compare incoming face embedding with stored image
3. ✅ **Vendor Role**: Ensure user has 'vendor' role
4. ✅ **PIC Exists**: Verify PIC is in the database
5. ✅ **PIC Face Match** *(if embedding provided)*: Compare incoming face embedding with stored image
6. ✅ **PIC Role**: Ensure PIC is DCFM or SOC (not vendor)
7. ✅ **Gate Exists**: Verify gate is registered
8. ✅ **Gate Active**: Ensure gate is not disabled
9. ✅ **Active Task**: Find active task for this vendor-PIC pair
10. ✅ **Time Window**: Verify current time is within task's allowed window
11. ✅ **Gate Authorization**: Verify this gate is authorized for this task

#### Response (Success)

```json
{
  "approved": true,
  "reason": "OK",
  "similarity": null
}
```

#### Response (Denied - Face Mismatch)

```json
{
  "approved": false,
  "reason": "Vendor face verification failed",
  "similarity": 0.32
}
```

**HTTP Status Codes:**
- `200`: Access approved
- `403`: Access denied (any validation failed)

## Face Recognition Details

### How It Works

1. **IoT Device**: Captures face image and generates 512-d embedding using InsightFace
2. **API Request**: Sends `vendor_face_id` + `vendor_embedding` to the API
3. **Server**:
   - Looks up vendor by `face_id`
   - Retrieves vendor's stored face image (Base64) from database
   - Generates embedding from stored image using InsightFace
   - Compares incoming embedding vs stored embedding using **Cosine Similarity**
   - If similarity > **0.40** (40%), face matches
4. **Response**: Proceeds with remaining validation steps if face matched

### Similarity Threshold

- **Default**: 0.40 (40% similarity)
- **Adjustable**: Edit `SIMILARITY_THRESHOLD` in `crud.py`
- **Typical Range**: 0.30-0.50 for InsightFace

### Embedding Format

InsightFace produces **512-dimensional** float vectors. Example:
```json
{
  "vendor_embedding": [
    0.0234, -0.1234, 0.5678, ..., 0.9012
  ]
}
```

## Example Usage

### Python Client Example

```python
import requests
import numpy as np

# Assume you have InsightFace running on IoT device
# and generated an embedding
vendor_embedding = [0.123, 0.456, ...]  # 512 floats

response = requests.post(
    "http://127.0.0.1:8002/api/access/validate",
    json={
        "vendor_face_id": "VENDOR_123",
        "pic_face_id": "PIC_456",
        "gate_id": "GATE_A1",
        "vendor_embedding": vendor_embedding.tolist(),
        "pic_embedding": pic_embedding.tolist()
    }
)

if response.status_code == 200:
    result = response.json()
    if result["approved"]:
        print("✅ Access granted!")
        # Open gate
    else:
        print(f"❌ Access denied: {result['reason']}")
else:
    print("⚠️ Error contacting API")
```

### cURL Example

```bash
curl -X POST "http://127.0.0.1:8002/api/access/validate" \
  -H "Content-Type: application/json" \
  -d '{
    "vendor_face_id": "VENDOR_123",
    "pic_face_id": "PIC_456",
    "gate_id": "GATE_A1",
    "vendor_embedding": [0.123, 0.456, ...],
    "pic_embedding": [0.789, 0.012, ...]
  }'
```

## Architecture

```
IoT Device (Camera)
    ↓ (Capture face, generate embedding with InsightFace)
    ↓
FastAPI Server (Port 8002)
    ↓ (Validate embedding, check database)
    ↓
MySQL/SQLite Database (Sentinel)
    ↓ (Return validation result)
    ↓
IoT Device
    ↓ (Open/deny gate)
```

## Database Schema

The API uses the existing Sentinel Laravel database schema:

- `users`: Vendor and PIC records with `face_id` and `face_image` (Base64)
- `gates`: Gate definitions with `gate_id`
- `tasks`: Active assignments linking vendors, PICs, and time windows
- `gate_task`: Pivot table for gate-task authorization
- `audit_logs`: Complete access attempt logging

## Troubleshooting

### InsightFace Model Download

On first run, InsightFace downloads models (~100MB). If you see:
```
Downloading: "models/buffalo_l/..."
```
Just wait for it to complete. This only happens once.

### Face Recognition Not Working

If face verification is bypassed (always returns `True`), check:
1. InsightFace is installed: `pip list | grep insightface`
2. Check logs for: `"Warning: insightface library not installed"`
3. Ensure `face_image` in database is valid Base64

### Port Already in Use

```bash
# Kill existing uvicorn processes
pkill -f uvicorn

# Or use a different port
uvicorn main:app --reload --port 8003
```

### Database Connection Error

Check your `.env` file:
- Ensure `DB_CONNECTION` is `mysql` or `sqlite`
- For MySQL: Verify credentials and database exists
- For SQLite: Ensure file path is absolute and file exists

## Development

### Adding New Endpoints

Edit `main.py`:
```python
@app.get("/your-endpoint")
def your_endpoint():
    return {"status": "ok"}
```

### Modifying Validation Logic

Edit `crud.py` → `validate_access()` function.

### Changing Face Recognition Model

Edit `crud.py` → `get_face_app()`:
```python
# Change model (buffalo_l, buffalo_s, etc.)
FACE_APP = FaceAnalysis(name='buffalo_s', providers=['CPUExecutionProvider'])
```

## Performance

- **Face Recognition**: ~50-100ms per face (CPU)
- **API Response**: ~100-300ms total (including DB queries)
- **Concurrent Requests**: Scales with `--workers` parameter

## Security Notes

- 🔒 This API should run on a **private network** or behind a firewall
- 🔑 Consider adding API key authentication for production
- 📝 All access attempts are logged in `audit_logs` table
- 🚫 Failed attempts include similarity scores for forensics

## License

Part of the Sentinel Access Control System.
