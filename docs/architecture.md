# Architecture

## System Overview

Sentinel is a monolithic Laravel application with a modular internal structure. The frontend and API are served from the same application.

```
┌─────────────────────────────────────────────────────────────────┐
│                        External Systems                          │
├──────────────────┬──────────────────────────────────────────────┤
│   Web Browser    │           IoT Face Scanners                   │
│   (Blade + JS)   │           (REST Client)                       │
└────────┬─────────┴────────────────────┬─────────────────────────┘
         │                              │
         ▼                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                      Laravel Application                         │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────────┐  │
│  │   Routes    │  │ Controllers │  │      Middleware         │  │
│  │  web.php    │──│ Dashboard   │──│  auth, role-based       │  │
│  │  api.php    │  │ Task/User   │  │                         │  │
│  └─────────────┘  │ Gate/Access │  └─────────────────────────┘  │
│                   └──────┬──────┘                               │
│                          │                                      │
│  ┌───────────────────────┼───────────────────────────────────┐  │
│  │                   Services                                 │  │
│  │  ┌──────────────────────┐  ┌──────────────────────────┐   │  │
│  │  │ AccessValidation     │  │ TaskService              │   │  │
│  │  │ Service              │  │                          │   │  │
│  │  │ - Validate vendor    │  │ - Create/revoke tasks    │   │  │
│  │  │ - Validate PIC       │  │ - Role-based visibility  │   │  │
│  │  │ - Check gate access  │  │ - Time window validation │   │  │
│  │  │ - Log attempts       │  │                          │   │  │
│  │  └──────────────────────┘  └──────────────────────────┘   │  │
│  └───────────────────────────────────────────────────────────┘  │
│                          │                                      │
│  ┌───────────────────────┼───────────────────────────────────┐  │
│  │                   Policies                                 │  │
│  │  TaskPolicy, UserPolicy, GatePolicy                       │  │
│  │  (RBAC enforcement)                                       │  │
│  └───────────────────────────────────────────────────────────┘  │
│                          │                                      │
│  ┌───────────────────────┼───────────────────────────────────┐  │
│  │                    Models                                  │  │
│  │  User, Task, Gate, AuditLog                               │  │
│  └───────────────────────┬───────────────────────────────────┘  │
│                          │                                      │
└──────────────────────────┼──────────────────────────────────────┘
                           │
                           ▼
                    ┌─────────────┐
                    │   SQLite    │
                    │   Database  │
                    └─────────────┘
```

---

## Directory Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   └── AccessController.php    # IoT validation endpoint
│   │   ├── DashboardController.php     # Role-based dashboard
│   │   ├── TaskController.php          # Task CRUD
│   │   ├── UserController.php          # User management
│   │   └── GateController.php          # Gate management
│   └── Middleware/
├── Models/
│   ├── User.php                        # With role helpers
│   ├── Task.php                        # Vendor-PIC assignments
│   ├── Gate.php                        # Access points
│   └── AuditLog.php                    # Activity tracking
├── Policies/
│   ├── TaskPolicy.php                  # Task authorization
│   ├── UserPolicy.php                  # User authorization
│   └── GatePolicy.php                  # Gate authorization
└── Services/
    ├── AccessValidationService.php     # Core validation logic
    └── TaskService.php                 # Business logic
```

---

## Database Schema

```
┌─────────────────┐       ┌─────────────────┐
│     users       │       │     gates       │
├─────────────────┤       ├─────────────────┤
│ id              │       │ id              │
│ name            │       │ name            │
│ email           │       │ location        │
│ password        │       │ gate_id (unique)│
│ role (enum)     │       │ is_active       │
│ face_id (unique)│       │ timestamps      │
│ face_image      │       └────────┬────────┘
│ timestamps      │                │
└────────┬────────┘                │
         │                         │
         │     ┌───────────────────┼───────────────────┐
         │     │                   │                   │
         ▼     ▼                   ▼                   │
┌─────────────────────┐   ┌─────────────────┐         │
│       tasks         │   │   gate_task     │◄────────┘
├─────────────────────┤   ├─────────────────┤
│ id                  │   │ gate_id (FK)    │
│ vendor_id (FK)      │   │ task_id (FK)    │
│ pic_id (FK)         │   └─────────────────┘
│ start_time          │
│ end_time            │
│ status (enum)       │
│ created_by (FK)     │
│ timestamps          │
└─────────────────────┘

┌─────────────────────┐
│    audit_logs       │
├─────────────────────┤
│ id                  │
│ action              │
│ entity_type         │
│ entity_id           │
│ user_id (FK)        │
│ details (JSON)      │
│ success             │
│ reason              │
│ timestamps          │
└─────────────────────┘
```

---

## Role-Based Access Control

| Permission | Vendor | DCFM | SOC |
|------------|--------|------|-----|
| View own tasks | ✅ | ✅ | ✅ |
| View all tasks | ❌ | ✅ | ✅ |
| Create tasks | ❌ | ✅ | ❌ |
| Revoke tasks | ❌ | ✅ | ❌ |
| Manage users | ❌ | ✅ | ❌ |
| Manage gates | ❌ | ✅ | ❌ |
| View audit logs | ❌ | ✅ | ✅ |

---

## Access Validation Flow

```
IoT Device                    Sentinel                     Database
    │                            │                            │
    │  POST /api/access/validate │                            │
    │  {vendor_face_id,          │                            │
    │   pic_face_id, gate_id}    │                            │
    ├───────────────────────────►│                            │
    │                            │  1. Find vendor by face_id │
    │                            ├───────────────────────────►│
    │                            │◄───────────────────────────┤
    │                            │                            │
    │                            │  2. Find PIC by face_id    │
    │                            ├───────────────────────────►│
    │                            │◄───────────────────────────┤
    │                            │                            │
    │                            │  3. Find active task       │
    │                            │     (vendor + PIC pair)    │
    │                            ├───────────────────────────►│
    │                            │◄───────────────────────────┤
    │                            │                            │
    │                            │  4. Check gate permission  │
    │                            │  5. Check time window      │
    │                            │  6. Log attempt            │
    │                            ├───────────────────────────►│
    │                            │                            │
    │  {approved: true/false}    │                            │
    │◄───────────────────────────┤                            │
```
