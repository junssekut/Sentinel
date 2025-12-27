# User Guide

## Getting Started

Sentinel is a web-based access control system for managing vendor escorts in data centers. This guide covers how to use the application based on your role.

---

## Roles

| Role | Description |
|------|-------------|
| **DCFM** | Data Center Facility Manager — Full access to users and tasks |
| **SOC** | Security Operation Center — Gate management and monitoring |
| **Vendor** | External party — Can only view their own assigned tasks |

---

## Logging In

1. Navigate to the Sentinel URL
2. Enter your email and password
3. Click **Log in**

Your role badge will appear in the navigation bar next to your name.

---

## Dashboard

The dashboard shows role-specific information:

### DCFM/SOC View
- **Active Tasks** — Number of currently active vendor assignments
- **Total Vendors** — Count of registered vendor accounts
- **Active Gates** — Number of operational access points
- **Today's Access** — Access validation attempts today (with denied count)
- **Recent Tasks** — Latest task assignments
- **Quick Actions** — Shortcuts to create tasks, users, and gates

### Vendor View
- **Active Tasks** — Your current task assignments
- **Completed Tasks** — Your finished tasks
- **Recent Tasks** — Your task history

---

## Managing Tasks

### Creating a Task (DCFM only)

1. Click **Tasks** in the navigation
2. Click **New Task** button
3. Fill in the form:
   - **Vendor** — Select the vendor for this visit
   - **PIC** — Select who will escort the vendor
   - **Start Time** — When access begins
   - **End Time** — When access ends
   - **Allowed Gates** — Check which gates the vendor can access
   - **Notes** — Optional description
4. Click **Create Task**

> **Note:** A vendor can only have one active task at a time.

### Viewing Tasks

- **DCFM/SOC:** See all tasks system-wide
- **Vendor:** See only your own tasks

Use the status filter to show Active, Completed, or Revoked tasks.

### Completing a Task (DCFM only)

1. Click on a task to view details
2. Click the **Complete** button
3. Task status changes to "Completed"

### Revoking a Task (DCFM only)

1. Click on a task to view details
2. Click the **Revoke** button
3. Task status changes to "Revoked" and access is immediately denied

---

## Managing Users

### Registering a User (DCFM only)

1. Click **Users** in the navigation
2. Click **New User** button
3. Fill in the form:
   - **Full Name** — User's display name
   - **Email** — Login email (must be unique)
   - **Password** — Account password
   - **Role** — Vendor, DCFM, or SOC
   - **Face Image** — Upload or drag-drop a face photo
4. Click **Register User**

The system automatically generates a unique **Face ID** for IoT integration.

### User Roles

| Role | Access Level |
|------|--------------|
| Vendor | View own tasks only |
| DCFM | Manage users and tasks, view gates |
| SOC | Manage gates, view all tasks |

---

## Managing Gates

### Adding a Gate (SOC only)

1. Click **Gates** in the navigation
2. Click **New Gate** button
3. Fill in the form:
   - **Gate Name** — E.g., "Main Entrance"
   - **Location** — E.g., "Building A, Floor 1"
   - **Description** — Optional details
   - **Active** — Whether the gate is operational
4. Click **Add Gate**

The system automatically generates a unique **Gate ID** for IoT devices.

### Door Integration (SOC only)

To link a gate to a physical door lock:

1. Edit the gate
2. Enter **Door ID** — Must match client DEVICE_ID
3. Enter **Solenoid IP** — ESP8266 device address
4. Save

### Gate Status

- **Active** — Gate can be used in task assignments
- **Inactive** — Gate is disabled and all access is denied

---

## How Access Validation Works

When a vendor approaches a gate:

1. **Face Scan** — IoT device scans vendor's face
2. **PIC Scan** — IoT device scans PIC's face
3. **Validation** — Device sends request to Sentinel API
4. **Decision** — Sentinel checks:
   - Both identities exist
   - Vendor and PIC are assigned together
   - Task is currently active (within time window)
   - Gate is allowed for this task
5. **Result** — Access approved or denied

> All access attempts are logged for auditing.

---

## Audit Logs

DCFM and SOC users can view recent access logs on the dashboard. Each log shows:

- Gate accessed
- Success/failure status
- Timestamp

---

## Tips

- **Plan ahead:** Create tasks before the vendor arrives
- **Set accurate times:** Tasks only work within their time window
- **Use specific gates:** Only allow access to necessary areas
- **Revoke immediately:** If a visit is cancelled, revoke the task
- **Check the dashboard:** Monitor for denied access attempts
