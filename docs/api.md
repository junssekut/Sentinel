# API Reference

Sentinel provides a REST API for IoT face-scanning devices to validate access requests.

---

## Base URL

```
Production: https://your-sentinel-instance.com/api
Development: http://127.0.0.1:8000/api
```

---

## Endpoints

### Access Validation

Validate if a vendor-PIC pair can access a specific gate.

```http
POST /api/access/validate
```

#### Request Headers

| Header | Value | Required |
|--------|-------|----------|
| Content-Type | application/json | Yes |
| Accept | application/json | Yes |

#### Request Body

```json
{
  "vendor_face_id": "string",
  "pic_face_id": "string",
  "gate_id": "string",
  "timestamp": "ISO-8601"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| vendor_face_id | string | Yes | Unique face identifier of the vendor |
| pic_face_id | string | Yes | Unique face identifier of the PIC |
| gate_id | string | Yes | Gate identifier (e.g., "GATE-MAIN-001") |
| timestamp | string | No | ISO-8601 timestamp of the request |

#### Response - Access Approved

```json
{
  "approved": true,
  "reason": "OK"
}
```

**HTTP Status: 200 OK**

#### Response - Access Denied

```json
{
  "approved": false,
  "reason": "Vendor not found"
}
```

**HTTP Status: 403 Forbidden**

#### Possible Denial Reasons

| Reason | Description |
|--------|-------------|
| `Vendor not found` | No user with this face_id exists |
| `Invalid vendor role` | User exists but is not a vendor |
| `PIC not found` | No user with this PIC face_id exists |
| `Invalid PIC role` | PIC user cannot be a vendor |
| `Gate not found` | No gate with this gate_id exists |
| `Gate is inactive` | Gate exists but is disabled |
| `No active task found for this vendor-PIC pair` | No task assigns this vendor to this PIC |
| `Task is outside valid time window` | Task exists but current time is outside start/end |
| `Gate not authorized for this task` | Task exists but this gate is not permitted |

---

## Examples

### cURL

```bash
# Successful validation
curl -X POST http://127.0.0.1:8000/api/access/validate \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "vendor_face_id": "VENDOR-abc123",
    "pic_face_id": "DCFM-xyz789",
    "gate_id": "GATE-MAIN-001"
  }'

# Response
{"approved":true,"reason":"OK"}
```

### Python

```python
import requests

response = requests.post(
    'http://127.0.0.1:8000/api/access/validate',
    json={
        'vendor_face_id': 'VENDOR-abc123',
        'pic_face_id': 'DCFM-xyz789',
        'gate_id': 'GATE-MAIN-001'
    },
    headers={'Accept': 'application/json'}
)

result = response.json()
if result['approved']:
    print('Access granted!')
else:
    print(f'Access denied: {result["reason"]}')
```

### Node.js

```javascript
const response = await fetch('http://127.0.0.1:8000/api/access/validate', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    vendor_face_id: 'VENDOR-abc123',
    pic_face_id: 'DCFM-xyz789',
    gate_id: 'GATE-MAIN-001'
  })
});

const result = await response.json();
console.log(result.approved ? 'Access granted' : `Denied: ${result.reason}`);
```

---

## Validation Logic

The API performs these checks in order:

1. **Vendor exists** — Face ID matches a user in the system
2. **Vendor role** — User must have role = "vendor"
3. **PIC exists** — PIC face ID matches a user in the system
4. **PIC role** — PIC must NOT be a vendor (must be DCFM or SOC)
5. **Gate exists** — Gate ID matches an active gate
6. **Active task** — An active task exists pairing this vendor with this PIC
7. **Time window** — Current time is between task's start_time and end_time
8. **Gate permission** — The gate is in the task's allowed gates list

All checks must pass for access to be approved.

---

## Rate Limiting

Currently no rate limiting is applied. For production, consider adding:

```php
// In routes/api.php
Route::middleware('throttle:60,1')->group(function () {
    Route::post('/access/validate', [AccessController::class, 'validate']);
});
```

---

## Error Handling

### Validation Errors (400)

```json
{
  "message": "The vendor_face_id field is required.",
  "errors": {
    "vendor_face_id": ["The vendor_face_id field is required."]
  }
}
```

### Server Errors (500)

```json
{
  "message": "Server Error"
}
```
