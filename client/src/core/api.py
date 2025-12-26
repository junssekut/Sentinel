import requests
from ..utils.helpers import SERVER_URL

class SentinelAPI:
    def __init__(self, server_url=SERVER_URL):
        self.server_url = server_url

    def start_session(self):
        """Start a new verification session."""
        try:
            resp = requests.post(f"{self.server_url}/api/session/start", json={}, timeout=5)
            if resp.status_code == 200:
                return resp.json()
            return None
        except Exception as e:
            print(f"API Error (start_session): {e}")
            return None

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
