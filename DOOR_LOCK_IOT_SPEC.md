# Door Lock IoT – Server-Driven Actuator Specification

## 1. Purpose
This document defines a **server-driven door lock IoT system** where the IoT device acts as a **pure actuator**.

The IoT device:
- Does **not** perform face recognition
- Does **not** perform RFID scanning
- Does **not** manage timing or access rules

All intelligence, timing, and authorization logic resides on the **server**.

This specification is written to be directly consumable by **AI agentic systems** for reasoning, planning, and implementation.

---

## 2. System Role Definition

### 2.1 Door Lock IoT Device (ESP8266)
**Role:** Passive command listener + physical actuator

**Responsibilities:**
- Connect to local Wi-Fi (Station Mode)
- Expose HTTP endpoints
- Execute `LOCK` and `UNLOCK` commands
- Control relay → solenoid door lock

**Non-Responsibilities:**
- No biometric processing
- No RFID handling
- No access decision logic
- No timers or auto-lock logic

---

### 2.2 Server (Main Orchestrator)
**Role:** Single source of truth

**Responsibilities:**
- Receive face recognition results from external systems
- Decide whether to unlock the door
- Send explicit commands to Door Lock IoT
- Handle all timing (e.g., auto-lock after 10 seconds)
- Maintain audit logs

The server fully controls **when** and **if** the door changes state.

---

## 3. External Face Recognition (Out of Scope)

Face recognition is **external** and **decoupled** from the Door Lock IoT.

Rules:
- The IoT device is unaware of face recognition
- The server only sends `/unlock` if conditions are satisfied
- If no approval condition is met, **no command is sent**

This document intentionally excludes face recognition implementation details.

---

## 4. High-Level Architecture

```
[ Face Recognition System ]
              |
        (decision result)
              |
           [ Server ]
              |
    HTTP command (/unlock, /lock)
              |
   [ Door Lock IoT (ESP8266) ]
              |
        Relay → Solenoid Lock
```

---

## 5. Network Configuration

### 5.1 Wi-Fi Mode
- Door Lock IoT runs in **Station Mode (STA)**
- Joins the same LAN as the server

### 5.2 Addressing
- Server uses static IP or DHCP reservation
- IoT device has fixed or discoverable IP

Example:
```
Server → http://192.168.1.50
```

---

## 6. Door State Model

### 6.1 States
- `LOCKED` (default)
- `UNLOCKED`

### 6.2 State Ownership
- State is **command-driven**
- IoT device does not infer or calculate transitions

---

## 7. Server-Controlled Flow

### 7.1 UNLOCK Flow
1. Face recognition system processes camera input
2. If **PIC is detected**:
   - PIC and all vendors are approved
3. Server sends command:
   ```
   POST /unlock
   ```
4. Door Lock IoT unlocks immediately

If no PIC is detected:
- Server sends **no command**
- Door remains locked

---

### 7.2 LOCK Flow
1. Server waits for configured duration (e.g., 10 seconds)
2. Server sends command:
   ```
   POST /lock
   ```
3. Door Lock IoT locks immediately

Timing logic is **never** implemented on the IoT device.

---

## 8. IoT HTTP API Contract

### 8.1 Unlock Endpoint
```
POST /unlock
```

**Effect:**
- Energize relay
- Unlock solenoid door lock

---

### 8.2 Lock Endpoint
```
POST /lock
```

**Effect:**
- De-energize relay
- Lock solenoid door lock

---

## 9. Failure & Safety Rules

### 9.1 Default Behavior
- On boot → door is **LOCKED**

### 9.2 Failure Scenarios
- If server is unreachable → no state change
- If command is not received → door remains in last state

Fail-safe behavior is **explicit and predictable**.

---

## 10. Security Baseline

Minimum requirements:
- Shared secret or API key
- Network isolation (LAN/VLAN)
- No public internet exposure

Security decisions are enforced at the **server layer**.

---

## 11. Design Principles

- Server-centric control
- Minimal IoT firmware logic
- Explicit commands only
- No hidden automation
- Clear separation of concerns

---

## 12. Non-Goals

This system explicitly does NOT:
- Perform biometric recognition
- Scan RFID
- Make autonomous access decisions
- Implement auto-lock timers on the IoT device

---

## 13. Future Extensions (Non-Binding)

Possible future additions without redesign:
- HTTPS / mutual auth
- Health check endpoint (`/status`)
- Event acknowledgment
- Cloud-based orchestrator

These are **optional** and not required for the current scope.

---

## 14. Summary

This specification defines a **pure, server-driven door lock IoT actuator**.

- The IoT device only listens and executes
- The server owns all intelligence and timing
- External systems influence decisions indirectly via the server

This design is intentionally simple, deterministic, and suitable for **agentic AI orchestration**.

