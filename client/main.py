import tkinter as tk
import os
from src.ui.main_window import FaceClientApp

# Mitigate OpenMP DLL clashes on Windows
os.environ.setdefault("KMP_DUPLICATE_LIB_OK", "TRUE")
os.environ.setdefault("OMP_NUM_THREADS", "1")

def main():
    root = tk.Tk()
    app = FaceClientApp(root)
    root.mainloop()

if __name__ == "__main__":
    main()
