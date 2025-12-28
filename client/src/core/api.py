import requests
from ..utils.helpers import SERVER_URL, DEVICE_ID

class SentinelAPI:
    def __init__(self, server_url=SERVER_URL):
        self.server_url = server_url

    def start_session(self):
        """Start a new verification session."""
        try:
            resp = requests.post(
                f"{self.server_url}/api/session/start", 
                json={"gate_id": DEVICE_ID}, 
                timeout=5
            )
            if resp.status_code == 200:
                return {"success": True, "data": resp.json()}
            return {"success": False, "error": f"Server returned {resp.status_code}"}
        except requests.exceptions.ConnectionError as e:
            return {"success": False, "error": f"Connection refused to {self.server_url}/api/session/start"}
        except requests.exceptions.Timeout:
            return {"success": False, "error": f"Connection timeout to {self.server_url}"}
        except Exception as e:
            return {"success": False, "error": str(e)}

    def enroll_face(self, payload):
        """Enroll a new face."""
        try:
            resp = requests.post(
                f"{self.server_url}/api/faces/enroll-from-image",
                json=payload,
                timeout=30,
            )
            resp.raise_for_status()
            return resp.json()
        except requests.exceptions.HTTPError as exc:
            try:
                error_detail = exc.response.json().get('detail', str(exc))
            except:
                error_detail = str(exc)
            raise Exception(error_detail)
        except Exception as e:
            raise e

    def scan_session(self, payload):
        """Send embedding for session verification."""
        try:
            resp = requests.post(
                f"{self.server_url}/api/session/scan", 
                json=payload, 
                timeout=5
            )
            return resp
        except Exception as e:
            print(f"API Error (scan_session): {e}")
            return None

    def send_heartbeat(self, device_id: str) -> dict:
        """Send heartbeat to Python server (which proxies to Laravel)."""
        if not device_id:
            return {"success": False, "error": "DEVICE_ID not configured"}
        try:
            resp = requests.post(
                f"{self.server_url}/api/heartbeat",
                json={"device_id": device_id},
                timeout=5
            )
            if resp.status_code == 200:
                return {"success": True, "data": resp.json()}
            elif resp.status_code == 404:
                return {"success": False, "error": f"Gate not found for device_id: {device_id}"}
            return {"success": False, "error": f"Server returned {resp.status_code}"}
        except Exception as e:
            return {"success": False, "error": str(e)}
