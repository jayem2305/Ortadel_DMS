from flask import Flask, jsonify, send_from_directory
import os
from datetime import datetime
from PIL import Image
import win32com.client
import pythoncom
from threading import Thread
from flask_cors import CORS
import traceback

app = Flask(__name__)
CORS(app)

SCAN_DIR = "scans"
os.makedirs(SCAN_DIR, exist_ok=True)

# Global flags
cancel_requested = False
scan_in_progress = False
last_result = None


def perform_scan():
    """Performs the actual scanning in a background thread."""
    global cancel_requested, scan_in_progress, last_result

    try:
        scan_in_progress = True
        cancel_requested = False
        pythoncom.CoInitialize()
        wia = win32com.client.Dispatch("WIA.CommonDialog")

        # Before device selection
        if cancel_requested:
            last_result = {"success": False, "error": "Scan canceled before starting."}
            return

        device = wia.ShowSelectDevice()
        if not device:
            last_result = {"success": False, "error": "No scanner selected"}
            return

        if len(device.Items) < 1:
            last_result = {"success": False, "error": "Selected scanner has no items"}
            return

        scan_item = device.Items[0]

        # Check before transfer
        if cancel_requested:
            last_result = {"success": False, "error": "Scan canceled before transfer."}
            return

        # Start scanning
        try:
            image = scan_item.Transfer()
            if cancel_requested:
                last_result = {"success": False, "error": "Scan canceled during transfer."}
                return
            print("✅ Scan transferred successfully")
        except Exception as e:
            tb = traceback.format_exc()
            print("Failed to transfer image:\n", tb)
            last_result = {"success": False, "error": f"Failed to transfer image: {str(e)}"}
            return

        # Save BMP
        temp_bmp_path = os.path.join(
            SCAN_DIR, f"scan_temp_{datetime.now().strftime('%Y%m%d_%H%M%S_%f')}.bmp"
        )
        image.SaveFile(temp_bmp_path)

        # Convert to JPEG
        img = Image.open(temp_bmp_path)
        jpeg_filename = f"scan_{datetime.now().strftime('%Y%m%d_%H%M%S')}.jpg"
        jpeg_path = os.path.join(SCAN_DIR, jpeg_filename)
        img.convert("RGB").save(jpeg_path, "JPEG")

        last_result = {"success": True, "file": jpeg_filename}

    except Exception as e:
        tb = traceback.format_exc()
        print("Scanner API error:\n", tb)
        last_result = {"success": False, "error": str(e)}

    finally:
        pythoncom.CoUninitialize()
        scan_in_progress = False


@app.route("/api/scan")
def scan_document():
    """Starts the scan in a separate thread."""
    global scan_in_progress, last_result, cancel_requested

    if scan_in_progress:
        return jsonify({"success": False, "error": "Scan already in progress."}), 400

    cancel_requested = False
    last_result = None

    thread = Thread(target=perform_scan)
    thread.start()

    return jsonify({"success": True, "message": "Scan started in background."})


@app.route("/api/status")
def scan_status():
    """Check current scan status."""
    global scan_in_progress, last_result
    if scan_in_progress:
        return jsonify({"status": "scanning"})
    if last_result:
        return jsonify(last_result)
    return jsonify({"status": "idle"})


@app.route("/api/cancel", methods=["GET", "POST"])
def cancel_scan():
    global cancel_requested
    cancel_requested = True
    print("❌ Scan cancel requested by user.")
    return jsonify({"success": True, "message": "Scan cancellation requested."})


@app.route("/files/<filename>")
def serve_file(filename):
    return send_from_directory(SCAN_DIR, filename)


if __name__ == "__main__":
    app.run(host="127.0.0.1", port=5001, debug=True)
