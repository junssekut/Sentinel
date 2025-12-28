import os
import tkinter.font as tkfont
from pathlib import Path
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

# Constants
SERVER_URL = os.getenv("SERVER_URL", "http://127.0.0.1:8001")
API_SECRET = os.getenv("API_SECRET", "dev-secret")
DEVICE_ID = os.getenv("DEVICE_ID", "")  # Unique ID for this device, also used as door_id
CAPTURE_INTERVAL = int(os.getenv("CAPTURE_INTERVAL", "5"))  # Seconds (legacy)
CAPTURE_INTERVAL_MS = int(os.getenv("CAPTURE_INTERVAL_MS", "0"))  # Milliseconds (takes priority if > 0)
CAMERA_INDEX = int(os.getenv("CAMERA_INDEX", "0"))
HEARTBEAT_INTERVAL = int(os.getenv("HEARTBEAT_INTERVAL", "60"))  # Seconds between heartbeats
MODEL_NAME = os.getenv("MODEL_NAME", "buffalo_l")
DET_SIZE = int(os.getenv("DET_SIZE", "320"))

# Paths
BASE_DIR = Path(__file__).parent.parent.parent
ASSETS_DIR = BASE_DIR / "assets"
MODEL_DIR = Path.home() / ".insightface"

COLORS = {
    "bg_window": "#F5F7FA",
    "bg_card": "#FFFFFF",
    "text_primary": "#1A1A2E",
    "text_secondary": "#6B7280",
    "accent": "#0066AE",
    "accent_hover": "#004D82",
    "accent_light": "#E6F0F8",
    "success": "#10B981",
    "success_bg": "#ECFDF5",
    "warning": "#F59E0B",
    "warning_bg": "#FFFBEB",
    "danger": "#EF4444",
    "danger_bg": "#FEF2F2",
    "border": "#E5E7EB",
    "shadow": "#00000015",
}

VIDEO_WIDTH = 640
VIDEO_HEIGHT = 480

# Fonts
FONT_FAMILY = "BricolageGrotesque"
FONT_FALLBACK = ("SF Pro Display", "Segoe UI", "Helvetica Neue", "Helvetica", "Arial")

def get_font(size: int, weight: str = "normal") -> tuple:
    """Get font tuple, trying custom font first then fallbacks"""
    return (FONT_FAMILY, size, weight)

def check_fonts():
    """Check if custom font is available and set it or a fallback globally"""
    global FONT_FAMILY
    available_fonts = tkfont.families()
    if FONT_FAMILY not in available_fonts:
        for fallback in FONT_FALLBACK:
            if fallback in available_fonts:
                FONT_FAMILY = fallback
                break
        else:
            FONT_FAMILY = "Helvetica"
