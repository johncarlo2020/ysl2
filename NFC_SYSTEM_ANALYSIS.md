# NFC/RFID System Analysis Report
**Date:** 2026-08-24  
**Status:** Mixed Implementation - Needs Consolidation

---

## 🔍 Current System Overview

Your RFID system has **TWO PARALLEL implementations** running simultaneously:

### 1️⃣ **WebSocket Hub System (hub.js)**
**Location:** `rfid-relay/hub.js`  
**Status:** ✅ Configured but NOT being used by browser clients

**How it works:**
- Runs on VPS at `127.0.0.1:3000` (proxied via Apache to `wss://host/nfc-ws`)
- HTTP endpoint at `127.0.0.1:3001/push` for Laravel integration
- Designed for 3 client types:
  - Relay clients (send UIDs with token)
  - Kiosk clients (`?type=kiosk&station=X`)
  - Admin clients (`?type=admin`)

**Features:**
- Smart routing (admin vs kiosk isolation)
- Assign mode blocking (prevents kiosk taps during admin assignment)
- Station-specific routing
- Auto-reconnection logic

**Current Issue:** ❌ **Browser clients are NOT connecting to this WebSocket**

---

### 2️⃣ **Pusher Broadcasting System**
**Status:** ✅ Currently Active and Used

**How it works:**
- Uses Laravel's broadcasting system with Pusher service
- Channels:
  - `rfid` - General RFID channel (admin page)
  - `rfid-reg-{N}` - Registration-specific channel
  - `rfid-station-{N}` - Station-specific channels (kiosk pages)
- Event: `RfidCardTapped` broadcasts to `rfid` channel

**Current Implementation:**
- **Admin page ([rfid.blade.php](resources/views/rfid.blade.php)):**
  ```javascript
  pusherClient.subscribe('rfid').bind('card.tapped', ...)
  // OR
  pusherClient.subscribe('rfid-reg-' + regId).bind('card.tapped', ...)
  ```

- **Kiosk page ([kiosk.blade.php](resources/views/kiosk.blade.php)):**
  ```javascript
  pusherKiosk.subscribe('rfid-station-{{ $station->id }}').bind('card.tapped', ...)
  ```

**Current Issue:** ⚠️ **Channel mismatch - Event broadcasts to 'rfid' but kiosks listen to 'rfid-station-X'**

---

## 🐛 **CRITICAL ISSUE FOUND**

### Channel Broadcasting Mismatch

**Problem:**
```php
// RfidCardTapped.php - Broadcasts to 'rfid' channel
public function broadcastOn(): array {
    return [new Channel('rfid')];
}
```

```javascript
// kiosk.blade.php - Listens to 'rfid-station-{id}' channel
pusherKiosk.subscribe('rfid-station-{{ $station->id }}')
```

**Result:** 🔴 **Kiosk pages NEVER receive Pusher broadcasts!**

Kiosks currently work ONLY via keyboard-wedge input, not via NFC reader + Pusher.

---

## 📊 Current Data Flow

### Admin RFID Assignment Flow ✅ (Works)
```
NFC Reader (USB) → Keyboard Wedge → Browser Input Field
    OR
NFC Reader (server.js/relay.js) → POST /rfid/receive → Pusher 'rfid' → Admin Page
```

### Kiosk Check-in Flow ⚠️ (Partial)
```
RFID Reader (USB) → Keyboard Wedge → Hidden Input → processRfid() ✅ WORKS

NFC Reader (server.js/relay.js) → POST /rfid/receive → Pusher 'rfid' 
    → 🔴 KIOSKS DON'T RECEIVE (wrong channel)
```

---

## 🔧 System Components Status

### Components Inventory

| Component | Location | Status | Usage |
|-----------|----------|--------|-------|
| **hub.js** | rfid-relay/hub.js | ✅ Ready | ❌ Not connected |
| **relay.js** | rfid-relay/relay.js | ✅ Ready | ❌ Not used |
| **server.js** | rfid-relay/server.js | ✅ Ready | ⚠️ May be used |
| **server.py** | rfid-relay/server.py | ✅ Ready | ⚠️ Alternative |
| **Pusher** | Laravel Broadcasting | ✅ Active | ✅ Used (admin) |
| **Keyboard Wedge** | Browser input capture | ✅ Active | ✅ Used (all pages) |

### Hardware Readers

**Supported:**
- ACR122U NFC/RFID Reader (USB)
- Any keyboard-wedge compatible RFID reader

**Current Mode:** Keyboard wedge (types UIDs as keyboard input)

---

## 🎯 Recommended Solutions

### Option 1: **Fix Pusher Channels** ⭐ (Recommended - Simplest)

**Pros:**
- Minimal changes
- Uses existing Pusher infrastructure
- No additional services needed

**Cons:**
- Relies on external Pusher service
- Monthly Pusher costs

**Implementation:**

1. **Create station-specific broadcast event:**

```php
// app/Events/RfidCardTappedAtStation.php
class RfidCardTappedAtStation implements ShouldBroadcastNow
{
    public string $uid;
    public int $stationId;

    public function __construct(string $uid, int $stationId)
    {
        $this->uid = strtoupper(trim($uid));
        $this->stationId = $stationId;
    }

    public function broadcastOn(): array
    {
        return [new Channel('rfid-station-' . $this->stationId)];
    }

    public function broadcastAs(): string
    {
        return 'card.tapped';
    }
}
```

2. **Update receiveRfid method:**

```php
// Broadcast to both general and station-specific channels
broadcast(new RfidCardTapped($uid));

if (!empty($stationId)) {
    broadcast(new RfidCardTappedAtStation($uid, (int)$stationId));
}
```

**Estimated Time:** 15 minutes

---

### Option 2: **Switch to WebSocket Hub** (Best for Self-Hosted)

**Pros:**
- No external dependencies
- No monthly costs
- Better control and customization
- Already coded in hub.js

**Cons:**
- Requires hub.js to be running 24/7
- More infrastructure to manage

**Implementation:**

1. **Start hub.js on VPS:**
```bash
cd rfid-relay
npm install
node hub.js
# OR use PM2 for process management
pm2 start hub.js --name rfid-hub
pm2 save
```

2. **Update kiosk.blade.php - replace Pusher with WebSocket:**

```javascript
// Replace Pusher code with:
(function connectWS() {
    var wsProto = location.protocol === 'https:' ? 'wss:' : 'ws:';
    var ws = new WebSocket(wsProto + '//' + location.host + '/nfc-ws?type=kiosk&station={{ $station->id }}');

    ws.onopen = function () {
        console.log('NFC relay connected');
    };

    ws.onmessage = function (event) {
        try {
            var data = JSON.parse(event.data);
            if (data.uid) processRfid(data.uid.toUpperCase());
        } catch (e) { /* ignore */ }
    };

    ws.onclose = function () {
        console.log('NFC relay disconnected — retrying in 3 s');
        setTimeout(connectWS, 3000);
    };

    ws.onerror = function () { ws.close(); };
})();
```

3. **Update rfid.blade.php - replace Pusher with WebSocket:**

```javascript
// Replace Pusher code with:
var adminWs = null;
(function connectWS() {
    var wsProto = location.protocol === 'https:' ? 'wss:' : 'ws:';
    var ws = new WebSocket(wsProto + '//' + location.host + '/nfc-ws?type=admin');
    adminWs = ws;

    ws.onopen = function () {
        console.log('NFC relay connected');
        if (modalOpen) {
            ws.send(JSON.stringify({ assignMode: true }));
        }
    };

    ws.onmessage = function (event) {
        try {
            var data = JSON.parse(event.data);
            var uid = data.uid;
            if (!uid) return;
            
            // ... rest of your existing logic
        } catch (e) { /* ignore */ }
    };

    ws.onclose = function () {
        adminWs = null;
        console.log('NFC relay disconnected — retrying in 3 s');
        setTimeout(connectWS, 3000);
    };

    ws.onerror = function () { ws.close(); };
})();

// Update modal handlers to send assignMode messages
$('#assignModal').on('show.bs.modal', function () {
    modalOpen = true;
    if (adminWs && adminWs.readyState === WebSocket.OPEN) {
        adminWs.send(JSON.stringify({ assignMode: true }));
    }
});

$('#assignModal').on('hidden.bs.modal', function () {
    modalOpen = false;
    if (adminWs && adminWs.readyState === WebSocket.OPEN) {
        adminWs.send(JSON.stringify({ assignMode: false }));
    }
});
```

4. **Configure Apache/Nginx to proxy WebSocket:**

**Apache:**
```apache
<VirtualHost *:443>
    ServerName my.lovenudebeautyhotel.com
    
    # WebSocket proxy
    ProxyPass /nfc-ws ws://127.0.0.1:3000/
    ProxyPassReverse /nfc-ws ws://127.0.0.1:3000/
    
    # ... rest of config
</VirtualHost>
```

**Nginx:**
```nginx
location /nfc-ws {
    proxy_pass http://127.0.0.1:3000;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host $host;
}
```

**Estimated Time:** 1-2 hours

---

### Option 3: **Hybrid Approach** (Current State + Fixes)

Keep keyboard-wedge as primary, use broadcasting as backup.

**Pros:**
- Most reliable (two input methods)
- Works even if network fails

**Cons:**
- More complex debugging
- Potential for duplicate triggers

**Implementation:**
- Fix Pusher channels (Option 1)
- Keep keyboard-wedge input active
- Add duplicate detection in JavaScript

**Estimated Time:** 30 minutes

---

## 📋 Testing Checklist

After implementing any solution:

### Kiosk Pages
- [ ] Open kiosk page in browser
- [ ] Check console for connection status
- [ ] Tap RFID card with reader
- [ ] Verify UID appears in input field
- [ ] Confirm check-in processes successfully
- [ ] Test with multiple rapid taps (cooldown)
- [ ] Test disconnect/reconnect scenario

### Admin Page
- [ ] Open admin RFID management
- [ ] Click "Assign" button
- [ ] Check modal opens
- [ ] Tap RFID card
- [ ] Verify UID auto-fills
- [ ] Confirm assignment saves
- [ ] Check kiosks are blocked during assignment
- [ ] Verify kiosks resume after modal closes

### Network Tests
- [ ] Test with slow network connection
- [ ] Test with WebSocket disconnection
- [ ] Test with Pusher disconnection
- [ ] Verify auto-reconnection works

---

## 🚦 Current System Status Summary

| Feature | Status | Notes |
|---------|--------|-------|
| Keyboard Wedge Input | ✅ Working | Primary input method |
| Pusher Admin Broadcast | ✅ Working | Receives on 'rfid' channel |
| Pusher Kiosk Broadcast | 🔴 **Broken** | Channel mismatch |
| WebSocket Hub | ⚠️ Ready | Not connected by browsers |
| NFC Reader Support | ✅ Ready | server.js/relay.js available |
| Rate Limiting | ✅ Working | Implemented |
| Activity Logging | ✅ Working | RfidLog model |
| Validation | ✅ Working | ValidRfidUid rule |

---

## 💡 Recommendations

### Immediate Actions (Critical)
1. **Fix Pusher channel mismatch** - Option 1 above (15 min)
2. **Test all kiosk pages** after fix
3. **Document which NFC readers are deployed** and their mode

### Short-term (This Week)
1. **Choose primary broadcast method** (Pusher or WebSocket)
2. **Remove unused code** from the non-selected method
3. **Update documentation** to reflect chosen architecture

### Long-term (This Month)
1. **Monitor Pusher usage/costs** if keeping Pusher
2. **Set up PM2 for hub.js** if switching to WebSocket
3. **Add connection status indicators** to UI
4. **Implement fallback logic** if using hybrid approach

---

## 📞 Next Steps

**Please decide:**
1. Which broadcast method do you prefer?
   - **Pusher** (easier, costs money, external dependency)
   - **WebSocket Hub** (self-hosted, free, needs hub.js running)
   - **Hybrid** (both methods active)

2. Are NFC readers currently deployed at stations?
   - If YES: Which ones? (server.js, relay.js, or just keyboard wedge?)
   - If NO: Just keyboard wedge is sufficient

Once decided, I can implement the appropriate solution.

---

**Report Generated:** 2026-08-24  
**System Version:** Current State Analysis
