import time, requests
from smartcard.CardMonitoring import CardMonitor, CardObserver
from smartcard.util import toHexString

LARAVEL_URL = "http://localhost/ysl2/public/rfid/receive"
RFID_TOKEN  = "ysl-rfid-secret-2026"

GET_UID = [0xFF, 0xCA, 0x00, 0x00, 0x00]

class Observer(CardObserver):
    def update(self, observable, actions):
        added, _ = actions
        for card in added:
            try:
                conn = card.createConnection()
                conn.connect()
                resp, sw1, sw2 = conn.transmit(GET_UID)
                if sw1 == 0x90:
                    uid = toHexString(resp).replace(" ", "").upper()
                    print("Card tapped:", uid)
                    try:
                        r = requests.post(
                            LARAVEL_URL,
                            json={"uid": uid},
                            headers={"X-RFID-Token": RFID_TOKEN},
                            timeout=5
                        )
                        print("Laravel:", r.status_code, r.text)
                    except Exception as e:
                        print("HTTP error:", e)
                conn.disconnect()
            except Exception as e:
                print("Card error:", e)

print("--- NFC Relay (Python) ---")
print("Waiting for ACR122U reader...")

monitor = CardMonitor()
monitor.addObserver(Observer())

try:
    while True:
        time.sleep(1)
except KeyboardInterrupt:
    print("Stopped.")
