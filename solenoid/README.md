# Sentinel Door Lock IoT

ESP8266-based server-driven door lock actuator with LED and buzzer feedback.

## Hardware Required

| Component | Quantity |
|-----------|----------|
| NodeMCU ESP8266 | 1 |
| Relay Module 1CH 5V | 1 |
| Solenoid Door Lock 12V | 1 |
| Power Adaptor 12V 1A | 1 |
| Green LED | 1 |
| Red LED | 1 |
| Active Buzzer 5V | 1 |
| Resistors 220Ω | 2 |
| Project Board 400 Hole | 1 |

## Wiring Diagram

### Pin Configuration

| NodeMCU Pin | Component | Description |
|-------------|-----------|-------------|
| D0 (GPIO16) | Relay IN | Controls solenoid lock |
| D1 (GPIO5) | Green LED (+) | Access granted indicator |
| D3 (GPIO0) | Red LED (+) | Access denied indicator |
| D8 (GPIO15) | Buzzer (+) | Audio feedback |
| 3V3 | Relay VCC | Power for relay |
| GND | Common ground | All components share ground |

### Connection Diagram

```
                    ┌─────────────────┐
                    │   NodeMCU       │
                    │   ESP8266       │
                    │                 │
  ┌─────────────────┤ D0 (GPIO16)     │  → Relay IN
  │       ┌─────────┤ D1 (GPIO5)      │  → Green LED (+)
  │       │   ┌─────┤ D3 (GPIO0)      │  → Red LED (+)
  │       │   │ ┌───┤ D8 (GPIO15)     │  → Buzzer (+)
  │       │   │ │   ├ 3V3             ├──────┐
  │       │   │ │   │ GND             ├────┐ │
  │       │   │ │   └─────────────────┘    │ │
  │       │   │ │                          │ │
  │       │   │ │   ┌─────────────┐        │ │
  │       │   │ └───┤ Buzzer (+)  ├────────┘ │
  │       │   │     └─────────────┘          │
  │       │   │                              │
  │       │   │     ┌─────────────┐          │
  │       │   └──R──┤ Red LED (+) ├──────────┤
  │       │         └─────────────┘          │
  │       │                                  │
  │       │         ┌─────────────┐          │
  │       └──────R──┤ Green LED(+)├──────────┤
  │                 └─────────────┘          │
  │                                          │
  │  ┌─────────────────────────────────┐     │
  │  │         Relay Module            │     │
  │  │                                 │     │
  └──┤ IN                          VCC ├─────┘
     │                             GND ├───────┐
     │                                 │       │
     │ COM ─────────────┐              │       │
     │ NO  ─────────┐   │              │       │
     └──────────────│───│──────────────┘       │
                    │   │                      │
                    │   │   ┌─────────────────┐│
                    │   │   │  12V Adaptor    ││
                    │   │   │    (+) (-)      ││
                    │   │   └──┬───────┬──────┘│
                    │   │      │       │       │
                    │   └──────┘       │       │
                    │                  │       │
              ┌─────┴──────────────────┴─────┐ │
              │        Solenoid Lock         │ │
              │         (+)    (-)           │ │
              └──────────────────────────────┘ │
                                               │
              (All GND connect together) ──────┘
```

> **Note:** R = 220Ω resistor for LEDs

---

## Arduino IDE Setup (Detailed Guide)

### Step 1: Install Arduino IDE

1. Download Arduino IDE from [arduino.cc/en/software](https://www.arduino.cc/en/software)
2. Install and open Arduino IDE

### Step 2: Add ESP8266 Board Support

1. Go to **File** → **Preferences**
2. In **"Additional Boards Manager URLs"**, add:
   ```
   http://arduino.esp8266.com/stable/package_esp8266com_index.json
   ```
3. Click **OK**
4. Go to **Tools** → **Board** → **Boards Manager**
5. Search for **"esp8266"**
6. Install **"esp8266 by ESP8266 Community"**
7. Wait for installation to complete

### Step 3: Select Board Settings

Go to **Tools** menu and set:

| Setting | Value |
|---------|-------|
| Board | NodeMCU 1.0 (ESP-12E Module) |
| Upload Speed | 115200 |
| CPU Frequency | 80 MHz |
| Flash Size | 4MB (FS:2MB OTA:~1019KB) |
| Port | Select your USB port (e.g., COM3 or /dev/cu.usbserial) |

### Step 4: Prepare the Files

**Important:** In Arduino IDE, the `.ino` file and its dependencies must be in the same folder.

The `solenoid/` folder contains:

```
solenoid/
├── solenoid_wifi.ino    ← Main file (UPLOAD THIS)
├── config.h             ← Your WiFi credentials (required)
├── config.h.example     ← Template for config.h
├── README.md            ← This documentation
└── (other files)        ← Not needed for upload
```

**What gets uploaded:**
- Only `solenoid_wifi.ino` is uploaded, but **Arduino IDE automatically includes** any `.h` files (like `config.h`) in the same folder during compilation.

### Step 5: Configure WiFi Credentials

1. If `config.h` doesn't exist, copy from template:
   ```bash
   cp config.h.example config.h
   ```

2. Edit `config.h` with your values:
   ```cpp
   #define WIFI_SSID     "Your-WiFi-Name"
   #define WIFI_PASSWORD "Your-WiFi-Password"
   #define API_SECRET    "your-secret-key"
   ```

> **Security:** `config.h` is gitignored to prevent committing credentials.

### Step 6: Open and Upload

1. Open Arduino IDE
2. Go to **File** → **Open**
3. Navigate to `solenoid/` folder
4. Select **`solenoid_wifi.ino`** and click Open
5. Click the **Upload** button (→ arrow icon) or press **Ctrl+U** / **Cmd+U**
6. Wait for compilation and upload to complete

### Step 7: Verify Upload

1. Open **Tools** → **Serial Monitor**
2. Set baud rate to **115200**
3. Press the **RST** button on NodeMCU
4. You should see:
   ```
   [SENTINEL] Door Lock IoT Starting...
   [WIFI] Connecting to Your-WiFi-Name
   .....
   [WIFI] Connected! IP: 192.168.x.x
   [HTTP] Server started on port 80
   [READY] Waiting for commands...
   ```
5. Note the IP address shown

---

## Which Files to Upload?

| File | Upload? | Purpose |
|------|---------|---------|
| `solenoid_wifi.ino` | ✅ **YES** | Main program - open this in Arduino IDE |
| `config.h` | ⚡ Auto-included | WiFi credentials - must exist in same folder |
| `config.h.example` | ❌ No | Template only |
| `solenoid.ino` | ❌ No | Old version (no LED/buzzer) |
| `preconfigured.ino` | ❌ No | RFID version (standalone) |

**In short:** Open `solenoid_wifi.ino`, make sure `config.h` exists, and click Upload!

---

## API Endpoints

| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| POST | `/unlock` | ✅ | Unlock door (green LED + double beep) |
| POST | `/lock` | ✅ | Lock door |
| GET | `/status` | ❌ | Get current door state and uptime |

**Authentication:** Include `X-API-Secret` header matching your `API_SECRET` in `config.h`.

## Testing the API

Replace `<ESP_IP>` with the IP address shown in Serial Monitor:

```bash
# Check status (no auth needed)
curl http://<ESP_IP>/status

# Unlock door
curl -X POST http://<ESP_IP>/unlock -H "X-API-Secret: your-secret-key"

# Lock door
curl -X POST http://<ESP_IP>/lock -H "X-API-Secret: your-secret-key"
```

### Example Response

```json
{"status":"unlocked","door":"unlocked"}
```

```json
{"door":"locked","uptime":120,"ip":"192.168.1.100"}
```

---

## Feedback Behavior

| Event | Green LED | Red LED | Buzzer |
|-------|-----------|---------|--------|
| Successful unlock | ON (0.5s) | - | 2 short beeps |
| Unauthorized request | - | ON (1.5s) | Long beep |
| WiFi connected | ON (0.5s) | - | 2 short beeps |
| WiFi failed | - | ON (1.5s) | Long beep → restart |

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| Port not showing | Install CH340 or CP2102 USB driver |
| Upload fails | Hold FLASH button while uploading |
| WiFi not connecting | Check SSID/password in config.h, ensure 2.4GHz network |
| 401 Unauthorized | X-API-Secret header doesn't match config.h |
| Can't find ESP IP | Check Serial Monitor at 115200 baud |
