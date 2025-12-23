# SPEC.md
## Sentinel — Data Center Escort Access Control System

---

## 1. Overview

**Sentinel** is an internal web application designed to enforce **vendor escort compliance** within secure data center environments.

Its primary function is to ensure that **vendors are always accompanied by their assigned PIC (Person in Charge)** when accessing restricted gates or doors. Sentinel acts as the **central authority** for identity management, task assignment, and access validation.

Sentinel integrates with **IoT face-scanning devices** (out of scope) that perform physical access checks and call Sentinel APIs for authorization decisions.

---

## 2. Core Engineering Principles

These principles are **non-negotiable** and must guide all implementation decisions.

### 2.1 Modularity First

- All components **must be modularized whenever possible**
- Each module should have:
  - A single responsibility
  - Clear boundaries
  - Minimal coupling with other modules
- Examples of modular separation:
  - Authentication
  - User management
  - Task management
  - Access validation
  - Audit logging
  - UI components (cards, tables, dialogs)

Avoid:
- God controllers
- Overloaded services
- Cross-cutting logic without abstraction

---

### 2.2 Simplicity Over Complexity

- **Expose simplicity, hide complexity**
- Complex logic must exist:
  - Behind services
  - Behind policies
  - Behind APIs
- UI and API consumers should interact with:
  - Simple inputs
  - Predictable outputs
  - Clear success/failure states

Rules:
- No leaking internal logic to UI
- No over-engineered abstractions
- Prefer clarity over cleverness

If a feature feels complex:
→ simplify the interface, not the user experience.

---

## 3. Stakeholders & Roles

### 3.1 Vendor
- External party accessing the data center
- Must be accompanied by an assigned PIC
- Can only access gates defined by their active task

### 3.2 DCFM (Data Center Facility Manager)
- Internal operator
- Responsibilities:
  - Register users
  - Assign tasks
  - Assign Vendor ↔ PIC relationships
- Full visibility and control over all tasks

### 3.3 SOC (Security Operation Center)
- Internal monitoring role
- Read-only access to:
  - All tasks
  - All vendor assignments
- No task modification permissions

---

## 4. Problem Statement

Manual escort enforcement fails because:
- PICs may leave vendors unattended
- Vendors can roam undetected
- There is no real-time, enforceable validation at gates

Sentinel enforces **pair-based access control**:

A vendor may only access a gate if their assigned PIC is physically present and validated together.

---

## 5. High-Level Solution Flow

At each gate/door:
1. Vendor face is scanned
2. PIC face is scanned
3. Sentinel validates:
   - Both identities exist
   - Vendor and PIC are assigned together
   - Task is active
   - Gate is allowed by the task
4. Result:
   - Approved → gate opens
   - Denied → access blocked

---

## 6. Scope Definition

### 6.1 In Scope
- Web application (frontend + backend)
- User authentication & authorization
- Face image storage (Base64)
- Task assignment & viewing
- IoT-facing validation APIs

### 6.2 Out of Scope
- IoT firmware
- Physical gate hardware control
- Face recognition algorithms

---

## 7. Technology Stack

Sentinel **must use the latest stable versions** available at the time of implementation.

### 7.1 Backend
- Laravel (latest stable)
  - REST-based architecture
  - Policy & gate authorization
  - Service-layer business logic
  - Secure authentication

### 7.2 Frontend
- Vite (latest stable)
  - Asset bundling
  - Fast development server

- Tailwind CSS (latest stable)
  - Utility-first styling
  - Design consistency
  - Responsive layouts

### 7.3 Architecture Constraints
- Monolithic Laravel application
- Modular internal structure
- Frontend and API served from the same application
- Blade templates preferred; JS frameworks optional

---

## 8. Functional Requirements

### 8.1 User Registration

Actors: Vendor, DCFM, SOC

Required fields:
- Full name
- Email (unique)
- Password (hashed)
- Role (Vendor / DCFM / SOC)
- Face image (Base64 encoded)

Rules:
- Face image is mandatory
- One face identity per user
- Email uniqueness enforced

---

### 8.2 User Login

- Email + password authentication
- Role-based access control (RBAC)
- Token-based or session-based authentication

---

### 8.3 Task Management

#### 8.3.1 Assign Task (DCFM only)

A Task represents a vendor visit session.

Task fields:
- Task ID
- Vendor ID
- PIC ID
- Allowed Gates (list)
- Start Time
- End Time
- Status (Active / Completed / Revoked)

Rules:
- Only one active task per vendor at a time
- Each task must have exactly one PIC
- Tasks outside their time window are invalid

---

### 8.4 View Tasks

| Role   | View Own Tasks | View All Tasks |
|--------|----------------|---------------|
| Vendor | Yes            | No            |
| DCFM  | Yes            | Yes           |
| SOC   | Yes            | Yes           |

Displayed information:
- Vendor name
- PIC name
- Task duration
- Allowed gates
- Task status

---

## 9. Face Data Handling

- Stored as Base64 strings
- Persisted securely
- Used only for identity verification
- No image processing performed in the web application

---

## 10. IoT Integration (API-Level)

### 10.1 Access Validation API

Endpoint:
POST /api/access/validate

Request payload:
    {
      "vendor_face_id": "string",
      "pic_face_id": "string",
      "gate_id": "string",
      "timestamp": "ISO-8601"
    }

Validation checks:
1. Vendor exists
2. PIC exists
3. Vendor & PIC are assigned together
4. Task is active
5. Gate is allowed

Response (approved):
    {
      "approved": true,
      "reason": "OK"
    }

Response (denied):
    {
      "approved": false,
      "reason": "Policy violation"
    }

---

## 11. UI / UX Specification

### 11.1 Design Principles
- Clean, modern, enterprise-grade
- Calm and authoritative
- Hide complexity behind clean interfaces
- Optimized for dashboards and monitoring

---

### 11.2 Color System

Primary Color:
- Sentinel Blue: #0066AE

Supporting Palette:
- Dark Navy: #0B1F33
- Slate Gray: #5F6C7B
- Light Gray: #F4F6F8
- Success: #2FBF71
- Warning: #F4A261
- Error: #E63946

---

### 11.3 Layout System — Bento Grid

- Bento-style grid layouts required
- Modular cards with:
  - Rounded corners (8–12px)
  - Soft shadows
  - Clear spacing
- Dashboard tiles:
  - Active tasks
  - Access attempts
  - SOC monitoring panels

Avoid:
- Dense full-width tables
- Overloaded screens

---

### 11.4 Floating UI Elements

Allowed:
- Floating Action Button (FAB) for:
  - Assign Task
  - Register User

Constraints:
- One FAB per screen
- Subtle elevation
- Must not block content

---

### 11.5 Typography

- Modern sans-serif fonts (Inter, Roboto, SF)
- Clear hierarchy:
  - Page headers
  - Card titles
  - Metadata text

---

## 12. Security Requirements

- Password hashing (bcrypt or equivalent)
- RBAC enforced on all endpoints
- Authentication required for all APIs (except login)
- Audit logs for:
  - Task creation
  - Task revocation
  - Access validation requests

---

## 13. Non-Functional Requirements

- Availability: 24/7 internal usage
- Validation latency < 1 second
- Scalable for concurrent gate checks
- Fault-tolerant task validation

---

## 14. Assumptions

- Face recognition handled externally
- IoT devices send recognized face IDs
- Stable network connectivity

---

## 15. Future Enhancements (Out of Scope)

- Real-time SOC alerts
- Vendor movement visualization
- Automatic task expiration
- Multi-PIC escort support

---

## 16. Success Criteria

- Vendors cannot access gates without assigned PIC
- All access attempts are auditable
- SOC has full visibility
- DCFM fully controls access policies