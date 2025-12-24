# Sentinel Door Lock IoT

ESP8266-based server-driven door lock actuator.

## Hardware Required

| Component | Quantity |
|-----------|----------|
| NodeMCU ESP8266 | 1 |
| Relay Module 1CH 5V | 1 |
| Solenoid Door Lock 12V | 1 |
| Power Adaptor 12V 1A | 1 |
| Project Board 400 Hole | 1 |

## Wiring Diagram

```
                    ┌─────────────────┐
                    │   NodeMCU       │
                    │   ESP8266       │
                    │                 │
  ┌─────────────────┤ D1 (GPIO5)      │
  │                 │ 3V3             ├──────┐
  │                 │ GND             ├────┐ │
  │                 └─────────────────┘    │ │
  │                                        │ │
  │  ┌─────────────────────────────────┐   │ │
  │  │         Relay Module            │   │ │
  │  │                                 │   │ │
  └──┤ IN                          VCC ├───┘ │
     │                             GND ├─────┘
     │                                 │
     │ COM ─────────────┐              │
     │ NO  ─────────┐   │              │
     └──────────────│───│──────────────┘
                    │   │
                    │   │   ┌─────────────────┐
                    │   │   │  12V Adaptor    │
                    │   │   │    (+) (-)      │
                    │   │   └──┬───────┬──────┘
                    │   │      │       │
                    │   └──────┘       │
                    │                  │
              ┌─────┴──────────────────┴─────┐
              │        Solenoid Lock         │
              │         (+)    (-)           │
              └──────────────────────────────┘
```

**Connections:**
- D1 (GPIO5) → Relay IN
- 3V3 → Relay VCC
- GND → Relay GND
- Relay NO → Solenoid (+)
- Relay COM → 12V Adaptor (+)
- Solenoid (-) → 12V Adaptor (-)

## Setup

1. **Install Arduino IDE** with ESP8266 board support
2. **Copy config:**
   ```bash
   cp config.h.example config.h
   ```
3. **Edit `config.h`** with your WiFi credentials
4. **Upload** `solenoid.ino` to NodeMCU

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/unlock` | Unlock door (energize relay) |
| POST | `/lock` | Lock door (de-energize relay) |
| GET | `/status` | Get current state |

**Security:** Include `X-API-Secret` header matching your `API_SECRET`.

## Testing

```bash
# Unlock
curl -X POST http://<ESP_IP>/unlock -H "X-API-Secret: sentinel-iot-secret"

# Lock
curl -X POST http://<ESP_IP>/lock -H "X-API-Secret: sentinel-iot-secret"

# Status
curl http://<ESP_IP>/status
```
