"""
Solenoid Client - HTTP client to control door lock IoT device
"""
import os
import asyncio
import httpx
from dotenv import load_dotenv

load_dotenv(os.path.join(os.path.dirname(__file__), '.env'))

IOT_URL = os.getenv("IOT_URL", "http://192.168.1.100")
IOT_SECRET = os.getenv("IOT_SECRET", "sentinel-iot-secret")
DOOR_UNLOCK_DURATION = int(os.getenv("DOOR_UNLOCK_DURATION", "10"))


async def _send_command(endpoint: str, gate_ip: str = None) -> dict:
    """Send command to solenoid IoT device"""
    base_url = gate_ip or IOT_URL
    url = f"{base_url}{endpoint}"
    headers = {"X-API-Secret": IOT_SECRET}
    
    try:
        async with httpx.AsyncClient(timeout=5.0) as client:
            response = await client.post(url, headers=headers)
            return {"success": True, "status": response.status_code, "data": response.json()}
    except httpx.ConnectError:
        print(f"[SOLENOID] Connection failed to {base_url}")
        return {"success": False, "error": "Connection failed"}
    except Exception as e:
        print(f"[SOLENOID] Error: {e}")
        return {"success": False, "error": str(e)}


async def unlock_door(gate_ip: str = None) -> dict:
    """Unlock the door. Optionally specify gate_ip for multi-door support."""
    target = gate_ip or IOT_URL
    print(f"[SOLENOID] Sending UNLOCK to {target}")
    return await _send_command("/unlock", gate_ip)


async def lock_door(gate_ip: str = None) -> dict:
    """Lock the door. Optionally specify gate_ip for multi-door support."""
    target = gate_ip or IOT_URL
    print(f"[SOLENOID] Sending LOCK to {target}")
    return await _send_command("/lock", gate_ip)


async def unlock_and_auto_lock(duration_seconds: int = None) -> dict:
    """Unlock door, wait, then lock"""
    duration = duration_seconds or DOOR_UNLOCK_DURATION
    
    unlock_result = await unlock_door()
    if not unlock_result.get("success"):
        return unlock_result

    print(f"[SOLENOID] Door unlocked, waiting {duration}s before locking...")
    await asyncio.sleep(duration)
    
    lock_result = await lock_door()
    return {
        "unlock": unlock_result,
        "lock": lock_result,
        "duration": duration
    }
