"""
RFID Relay Launcher  –  Auto-Install Edition
=============================================
On first run (as Administrator) this launcher automatically installs:
  1. Node.js LTS          – runtime for server.js
  2. Visual Studio Build Tools (C++ workload) – needed to compile nfc-pcsc
  3. ACR122U / PC/SC RFID driver  – via the Windows Smart Card service
     (Windows 10/11 auto-installs the CCID driver when the reader is plugged in;
      this launcher just ensures the Smart Card service is running.)

After prerequisites are satisfied it runs:
  • npm install  (first run only)
  • node server.js  (silently, in background)
  • System-tray icon with Stop / Open-log options

Build into a standalone .exe:
  pip install pyinstaller pystray pillow
  pyinstaller --onefile --noconsole --name "RFID Relay" launcher.py
"""

# ---------------------------------------------------------------------------
# Standard library
# ---------------------------------------------------------------------------
import ctypes
import os
import shutil
import subprocess
import sys
import threading
import time
import winreg

# ---------------------------------------------------------------------------
# Third-party (bundled by PyInstaller)
# ---------------------------------------------------------------------------
import tkinter as tk
from tkinter import ttk, messagebox

try:
    import pystray
    from PIL import Image, ImageDraw
    HAS_TRAY = True
except ImportError:
    HAS_TRAY = False

# ---------------------------------------------------------------------------
# Configuration
# ---------------------------------------------------------------------------
WINGET_NODE_ID  = "OpenJS.NodeJS.LTS"
WINGET_VSBT_ID  = "Microsoft.VisualStudio.BuildTools"
VSBT_WORKLOAD   = (
    "--quiet --wait --norestart --nocache "
    "--add Microsoft.VisualStudio.Workload.VCTools --includeRecommended"
)
# Known Node.js install paths (after MSI installs)
NODE_PATHS = [
    r"C:\Program Files\nodejs",
    r"C:\Program Files (x86)\nodejs",
]

# ===========================================================================
# Utility helpers
# ===========================================================================

def base_dir() -> str:
    """Folder containing the .exe (or this script) — not PyInstaller's temp dir."""
    if getattr(sys, "frozen", False):
        return os.path.dirname(sys.executable)
    return os.path.dirname(os.path.abspath(__file__))


def is_admin() -> bool:
    try:
        return bool(ctypes.windll.shell32.IsUserAnAdmin())
    except OSError:
        return False


def elevate_and_exit():
    """Re-launch self with UAC elevation, then quit the current (low-priv) process."""
    exe    = sys.executable
    params = " ".join(f'"{a}"' for a in sys.argv)
    ctypes.windll.shell32.ShellExecuteW(None, "runas", exe, params, None, 1)
    sys.exit(0)


def refresh_path():
    """Pull PATH from registry so newly-installed tools are visible in this process."""
    parts = []
    for hive, key in [
        (winreg.HKEY_LOCAL_MACHINE,
         r"SYSTEM\CurrentControlSet\Control\Session Manager\Environment"),
        (winreg.HKEY_CURRENT_USER, r"Environment"),
    ]:
        try:
            with winreg.OpenKey(hive, key) as k:
                val, _ = winreg.QueryValueEx(k, "Path")
                parts.append(val)
        except FileNotFoundError:
            pass
    if parts:
        os.environ["PATH"] = os.pathsep.join(parts)
    # Also add known Node.js locations just in case
    for p in NODE_PATHS:
        if os.path.isdir(p) and p not in os.environ["PATH"]:
            os.environ["PATH"] = p + os.pathsep + os.environ["PATH"]


def find_exe(name: str) -> str | None:
    """Return full path of an executable, checking both PATH and NODE_PATHS."""
    found = shutil.which(name)
    if found:
        return found
    for d in NODE_PATHS:
        candidate = os.path.join(d, name + ".cmd")
        if os.path.isfile(candidate):
            return candidate
        candidate = os.path.join(d, name + ".exe")
        if os.path.isfile(candidate):
            return candidate
    return None


# ===========================================================================
# Prerequisite detection
# ===========================================================================

def node_installed() -> bool:
    if find_exe("node"):
        return True
    for p in NODE_PATHS:
        if os.path.isfile(os.path.join(p, "node.exe")):
            return True
    return False


def vsbt_installed() -> bool:
    """Check that MSVC cl.exe (x64) is present anywhere under Program Files."""
    search_roots = [
        r"C:\Program Files\Microsoft Visual Studio",
        r"C:\Program Files (x86)\Microsoft Visual Studio",
        r"C:\BuildTools",
    ]
    for root in search_roots:
        for dirpath, _dirs, files in os.walk(root):
            if "cl.exe" in files and "x64" in dirpath.lower():
                return True
    # Also ask vswhere if it exists
    vswhere = (
        r"C:\Program Files (x86)\Microsoft Visual Studio"
        r"\Installer\vswhere.exe"
    )
    if os.path.isfile(vswhere):
        r = subprocess.run(
            [vswhere, "-latest", "-products", "*",
             "-requires", "Microsoft.VisualCpp.Tools.HostX64.TargetX64"],
            capture_output=True, text=True,
        )
        return bool(r.stdout.strip())
    return False


def smart_card_running() -> bool:
    r = subprocess.run(["sc", "query", "SCardSvr"],
                       capture_output=True, text=True)
    return "RUNNING" in r.stdout


def enable_smart_card():
    subprocess.run(["sc", "config", "SCardSvr", "start=auto"],
                   capture_output=True)
    subprocess.run(["sc", "start", "SCardSvr"],
                   capture_output=True)


# ===========================================================================
# winget helper
# ===========================================================================

def winget_install(pkg_id: str, extra_override: str | None = None) -> tuple[bool, str]:
    cmd = [
        "winget", "install", pkg_id,
        "--silent",
        "--accept-package-agreements",
        "--accept-source-agreements",
        "--scope", "machine",
    ]
    if extra_override:
        cmd += ["--override", extra_override]
    r = subprocess.run(cmd, capture_output=True, text=True)
    return r.returncode == 0, (r.stdout + r.stderr).strip()


# ===========================================================================
# Setup progress window (tkinter)
# ===========================================================================

class SetupWindow(tk.Toplevel):
    """Modal-ish progress window shown during first-time setup."""

    def __init__(self, master):
        super().__init__(master)
        self.title("RFID Relay – First-time Setup")
        self.resizable(False, False)
        self.protocol("WM_DELETE_WINDOW", lambda: None)   # prevent close
        self._build_ui()
        self._center()
        self.grab_set()

    def _build_ui(self):
        pad = {"padx": 24, "pady": 6}

        tk.Label(self, text="RFID Relay  –  Setting up",
                 font=("Segoe UI", 13, "bold")).pack(pady=(20, 2))
        tk.Label(self, text="Please wait while prerequisites are installed.",
                 font=("Segoe UI", 9), fg="#444").pack(**pad)

        self._status_var = tk.StringVar(value="Starting…")
        self._status_lbl = tk.Label(self, textvariable=self._status_var,
                                    font=("Segoe UI", 10, "bold"),
                                    wraplength=420, justify="left")
        self._status_lbl.pack(**pad)

        self._detail_var = tk.StringVar()
        tk.Label(self, textvariable=self._detail_var,
                 font=("Segoe UI", 8), fg="#666",
                 wraplength=420, justify="left").pack(**pad)

        self._bar = ttk.Progressbar(self, length=440, mode="indeterminate")
        self._bar.pack(padx=24, pady=10)
        self._bar.start(12)

        self._steps_var = tk.StringVar()
        tk.Label(self, textvariable=self._steps_var,
                 font=("Segoe UI", 8), fg="#888").pack(pady=(0, 20))

    def _center(self):
        self.update_idletasks()
        w = self.winfo_reqwidth()
        h = self.winfo_reqheight()
        sw, sh = self.winfo_screenwidth(), self.winfo_screenheight()
        self.geometry(f"{w}x{h}+{(sw-w)//2}+{(sh-h)//2}")

    def set_status(self, msg: str, detail: str = "", step: str = ""):
        self._status_var.set(msg)
        self._detail_var.set(detail)
        if step:
            self._steps_var.set(step)
        self.update()

    def close(self):
        self._bar.stop()
        self.grab_release()
        self.destroy()


# ===========================================================================
# Tray icon helpers
# ===========================================================================

def _make_tray_icon() -> "Image.Image":
    size = 64
    img  = Image.new("RGBA", (size, size), (0, 0, 0, 0))
    draw = ImageDraw.Draw(img)
    draw.ellipse([4, 4, size - 4, size - 4], fill=(34, 197, 94))
    return img


# ===========================================================================
# Main
# ===========================================================================

def main():
    root = tk.Tk()
    root.withdraw()

    bd        = base_dir()
    server_js = os.path.join(bd, "server.js")

    if not os.path.isfile(server_js):
        messagebox.showerror(
            "RFID Relay",
            f"server.js not found next to the launcher.\n\nExpected:\n{server_js}",
        )
        sys.exit(1)

    # ── Detect what needs installing ──────────────────────────────────────
    need_node  = not node_installed()
    need_vsbt  = not vsbt_installed()
    need_npm   = not os.path.isdir(os.path.join(bd, "node_modules"))
    need_scard = not smart_card_running()

    needs_install = need_node or need_vsbt

    # ── Elevation check ───────────────────────────────────────────────────
    if needs_install and not is_admin():
        ans = messagebox.askyesno(
            "RFID Relay – Administrator required",
            "First-time setup needs to install:\n"
            + ("  • Node.js LTS\n"                  if need_node else "")
            + ("  • Visual Studio Build Tools (C++)\n" if need_vsbt else "")
            + "\nClick Yes to restart as Administrator.",
        )
        if ans:
            elevate_and_exit()
        sys.exit(0)

    # ── Setup progress window ─────────────────────────────────────────────
    if needs_install or need_npm or need_scard:
        total   = sum([need_node, need_vsbt, need_npm or need_scard])
        current = [0]
        win     = SetupWindow(root)
        error   = [None]

        def step(label, detail=""):
            current[0] += 1
            win.set_status(label, detail,
                           step=f"Step {current[0]} of {total}")

        def do_setup():
            # ── 1. Node.js ───────────────────────────────────────────────
            if need_node:
                step("Installing Node.js LTS…",
                     "Downloading via Windows Package Manager. This may take a few minutes.")
                ok, out = winget_install(WINGET_NODE_ID)
                if not ok:
                    error[0] = (
                        "Node.js installation failed.\n\n"
                        "Please install it manually from https://nodejs.org\n\n"
                        + out
                    )
                    win.after(0, win.close)
                    return
                refresh_path()

            # ── 2. Visual Studio Build Tools ─────────────────────────────
            if need_vsbt:
                step("Installing Visual Studio Build Tools (C++)…",
                     "Large download (~2 GB). This may take 5–20 minutes. Please be patient.")
                ok, out = winget_install(WINGET_VSBT_ID, extra_override=VSBT_WORKLOAD)
                if not ok:
                    # Non-fatal: warn but continue — might already be partially installed
                    error[0] = (
                        "WARNING: VS Build Tools install may have failed.\n\n"
                        "If 'npm install' fails, run this command manually:\n"
                        "  winget install Microsoft.VisualStudio.BuildTools\n\n"
                        + out
                    )

            # ── 3. Smart Card service (RFID / ACR122U) ───────────────────
            if need_scard:
                step("Enabling Smart Card service (required for RFID reader)…",
                     "Windows handles the ACR122U CCID driver automatically when the "
                     "reader is plugged in. Just ensure the Smart Card service is running.")
                enable_smart_card()

            # ── 4. npm install ────────────────────────────────────────────
            if not os.path.isdir(os.path.join(bd, "node_modules")):
                step("Installing npm packages (nfc-pcsc)…",
                     "Compiling native addons — requires VS Build Tools installed above.")
                npm_exe = find_exe("npm") or "npm"
                result  = subprocess.run(
                    [npm_exe, "install"],
                    cwd=bd,
                    capture_output=True,
                    text=True,
                )
                if result.returncode != 0:
                    error[0] = (
                        "npm install failed.\n\n"
                        + (result.stderr or result.stdout or "Unknown error")
                    )

            win.after(0, win.close)

        threading.Thread(target=do_setup, daemon=True).start()
        root.mainloop()

        if error[0]:
            if error[0].startswith("WARNING"):
                messagebox.showwarning("RFID Relay – Setup Warning", error[0])
            else:
                messagebox.showerror("RFID Relay – Setup Failed", error[0])
                sys.exit(1)

        # If Node.js was just installed offer a reboot (PATH / env needs it)
        if need_node:
            ans = messagebox.askyesno(
                "Restart recommended",
                "Node.js was just installed.\n\n"
                "A restart is recommended to ensure PATH is updated.\n\n"
                "Restart now?",
            )
            if ans:
                subprocess.run(
                    ["shutdown", "/r", "/t", "15",
                     "/c", "Restarting to finish RFID Relay setup…"]
                )
                sys.exit(0)

    # ── Final npm install guard ───────────────────────────────────────────
    refresh_path()
    if not os.path.isdir(os.path.join(bd, "node_modules")):
        npm_exe = find_exe("npm") or "npm"
        r = subprocess.run([npm_exe, "install"], cwd=bd,
                           capture_output=True, text=True)
        if r.returncode != 0:
            messagebox.showerror("RFID Relay – npm install failed",
                                 r.stderr or r.stdout)
            sys.exit(1)

    # ── Launch server.js ──────────────────────────────────────────────────
    node_exe = find_exe("node") or "node"
    proc = subprocess.Popen(
        [node_exe, "server.js"],
        cwd=bd,
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
        text=True,
        creationflags=subprocess.CREATE_NO_WINDOW,
    )

    def _log():
        log_path = os.path.join(bd, "relay.log")
        with open(log_path, "a", encoding="utf-8") as f:
            f.write(f"\n--- Started {time.strftime('%Y-%m-%d %H:%M:%S')} ---\n")
            for line in proc.stdout:
                f.write(line)
                f.flush()

    threading.Thread(target=_log, daemon=True).start()

    # ── System-tray icon ──────────────────────────────────────────────────
    def stop_server(icon=None, _item=None):
        proc.terminate()
        if icon:
            icon.stop()
        root.quit()

    if HAS_TRAY:
        icon = pystray.Icon(
            "rfid-relay",
            _make_tray_icon(),
            "RFID Relay (running)",
            menu=pystray.Menu(
                pystray.MenuItem("RFID Relay  –  running", None, enabled=False),
                pystray.MenuItem(
                    "Open log file",
                    lambda _i, _it: os.startfile(os.path.join(bd, "relay.log")),
                ),
                pystray.Menu.SEPARATOR,
                pystray.MenuItem("Stop & Exit", stop_server),
            ),
        )

        def _watch():
            proc.wait()
            icon.stop()
            root.quit()

        threading.Thread(target=_watch, daemon=True).start()
        threading.Thread(target=icon.run, daemon=False).start()
        root.mainloop()
    else:
        print("RFID Relay running. Close this window or press Ctrl+C to stop.")
        try:
            proc.wait()
        except KeyboardInterrupt:
            proc.terminate()


if __name__ == "__main__":
    main()

