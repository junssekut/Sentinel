# Sentinel Access Control API (Python FastAPI)

This is a Python FastAPI implementation of the Sentinel Access Control API, designed to run as a separate service for IoT integration.

## Prerequisites

- Python 3.9+
- MySQL Database (Sentinel DB)

## Installation

1. Create a virtual environment (optional but recommended):
   ```bash
   python -m venv venv
   source venv/bin/activate  # On Windows: venv\Scripts\activate
   ```

2. Install dependencies:
   ```bash
   pip install -r requirements.txt
   ```

3. Environment Variables:
   The application automatically loads the `.env` file from the parent directory (`../.env`). Ensure your database credentials in `.env` are correct.

## Running the Server

Run the development server:
```bash
uvicorn main:app --reload --port 8000
```

The API will be available at:
- **API Root**: http://127.0.0.1:8000
- **Validation Endpoint**: `POST /api/access/validate`
- **Interactive Documentation**: http://127.0.0.1:8000/docs
- **Alternative Docs**: http://127.0.0.1:8000/redoc

## API Endpoints

### POST /api/access/validate

Validates an access request from an IoT device.

**Payload:**
```json
{
  "vendor_face_id": "string",
  "pic_face_id": "string",
  "gate_id": "string",
  "timestamp": "2023-01-01T12:00:00"
}
```

**Response:**
```json
{
  "approved": true,
  "reason": "OK"
}
```
