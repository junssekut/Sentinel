# Changelog

All notable changes to Sentinel from commit `5139ccd` onwards.

## [Unreleased] - 2025-12-28

### ✨ Features

#### Vendor Cards on Task Creation (`1596253`)
- Task creation form now displays vendor cards with profile photo, name, and email
- **Affected:** `web/resources/views/tasks/create.blade.php`

#### Face Detection Bounding Boxes (`ff6e17d`)
- Client app draws bounding boxes around detected faces with recognition status
- Color-coded boxes: green (recognized), yellow (unrecognized)
- Added `CAPTURE_INTERVAL_MS` environment variable for millisecond-level control
- Added `@stack('scripts')` support in Laravel layout
- **Affected:** `client/`, `web/`

#### Multi-Vendor Tasks (`90dd1b8`)
- Tasks now support multiple vendors (many-to-many relationship)
- New `task_vendor` pivot table migration
- Updated task views to display multiple vendors
- **Affected:** `web/` (migrations, models, views, controllers)

#### Heartbeat System (`5df6396`)
- Added heartbeat system for gate online/offline status tracking
- Client sends periodic heartbeats to server
- Gate status displayed in web interface
- **Affected:** `client/`, `server/`, `web/`

---

### 🐛 Bug Fixes

#### Solenoid Demo Mode (`2a74f7d`)
- Demo mode adjustments for IoT solenoid device
- Archived original code for reference
- **Affected:** `solenoid/`

#### Session Vendor Loading (`cb9dcc6`)
- Fixed vendor loading in session router for multi-vendor tasks
- **Affected:** `server/routers/session.py`

#### Vendor Table Access Error (`96440d0`)
- Fixed all references from deprecated `vendor_id` column to `vendors` relationship
- Updated dashboard, gate views, audit logs, and seeders
- **Affected:** `server/`, `web/`

---

### 📊 Components Changed

| Component | Changes |
|-----------|---------|
| **Client** | Face bounding boxes, heartbeat, `CAPTURE_INTERVAL_MS` |
| **Server** | Heartbeat endpoint, multi-vendor support |
| **Web** | Multi-vendor tasks, vendor cards UI, heartbeat status |
| **Solenoid** | Demo mode adjustments |

---

### 📝 Commit Summary

| Hash | Message | Date |
|------|---------|------|
| `1596253` | feat: vendor cards on adding to task | Dec 28, 17:48 |
| `ff6e17d` | feat: added bounding box for faces detected | Dec 28, 17:44 |
| `2a74f7d` | fix: demo purposes code on solenoid device | Dec 28, 16:44 |
| `cb9dcc6` | fix(server): vendor not loaded correctly | Dec 28, 15:07 |
| `96440d0` | fix: accessing vendor table causes error | Dec 28, 14:26 |
| `90dd1b8` | feat: fix multi vendors view | Dec 28, 14:17 |
| `5df6396` | feat: add heartbeat system & integrated w/ client and server | Dec 28, 13:58 |
