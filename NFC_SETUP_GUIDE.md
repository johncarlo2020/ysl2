# NFC/RFID Setup Guide - Pusher Implementation
**Date:** 2026-08-24  
**Status:** ✅ Fixed and Ready

---

## 🎉 What Was Fixed

### Issue Resolved
**Pusher Channel Mismatch** - Kiosk pages were listening to `rfid-station-{id}` but events were only broadcasting to the `rfid` channel.

### Solution Implemented
1. ✅ Created `RfidCardTappedAtStation` event for station-specific broadcasts
2. ✅ Updated `receiveRfid()` to broadcast to both channels:
   - `rfid` - General channel (admin pages)
   - `rfid-station-{X}` - Station-specific channels (kiosk pages)
3. ✅ Updated imports in `StationController`

---

## 🚀 Quick Setup Instructions

### Prerequisites
- NFC/RFID reader (ACR122U or keyboard-wedge compatible)
- Node.js installed on the reader machine
- Pusher account (free tier works)

---

## 📋 Setup Steps

### Step 1: Configure Environment Variables

**On VPS (Laravel):**

Edit `.env` file and add/update:

```bash
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=2147496
PUSHER_APP_KEY=8e90c33567599ca7143e
PUSHER_APP_SECRET=fb29453503976eb53bcd
PUSHER_APP_CLUSTER=us2
```

**Note:** Replace with your actual Pusher credentials from https://dashboard.pusher.com

---

### Step 2: Install Pusher PHP SDK (if not already installed)

```bash
cd c:\Users\johnc\OneDrive\Documents\8.1\ysl2
composer require pusher/pusher-php-server
```

---

### Step 3: Set Up NFC Reader Relay

**Option A: Using relay.js (Direct Pusher Push)**

Navigate to the relay folder:
```bash
cd rfid-relay
npm install
```

**For Admin Desk (General RFID channel):**
```bash
node relay.js
```

**For Station 1:**
```bash
STATION_ID=1 node relay.js
```

**For Station 2:**
```bash
STATION_ID=2 node relay.js
```

**For Station 3:**
```bash
STATION_ID=3 node relay.js
```

**For Station 4:**
```bash
STATION_ID=4 node relay.js
```

**For Registration Desk:**
```bash
REG_ID=1 node relay.js
```

---

**Option B: Using server.js (Via Laravel Endpoint)**

This posts to Laravel which then broadcasts via Pusher:

```bash
node server.js
```

With station ID:
```bash
STATION_ID=1 node server.js
```

---

### Step 4: Keep Relay Running (Production)

Use PM2 for process management:

```bash
# Install PM2
npm install -g pm2

# Start relay for admin desk
pm2 start relay.js --name rfid-admin

# Start relay for station 1
pm2 start relay.js --name rfid-station-1 -- --env STATION_ID=1

# Start relay for station 2
pm2 start relay.js --name rfid-station-2 -- --env STATION_ID=2

# Save PM2 configuration
pm2 save

# Setup PM2 to start on boot
pm2 startup
```

---

## 🔍 How It Works

### Data Flow Diagram

```
┌─────────────────┐
│  NFC Reader     │
│  (ACR122U)      │
└────────┬────────┘
         │ USB
         ↓
┌─────────────────┐
│   relay.js      │
│  (Mac/PC/VPS)   │
└────────┬────────┘
         │
         ├──→ Direct to Pusher (Option A)
         │    Channel: rfid-station-{id}
         │
         └──→ POST to Laravel (Option B)
              /rfid/receive
              └──→ Broadcasts to Pusher
                   - rfid (admin)
                   - rfid-station-{id} (kiosks)
                   
         ↓
┌─────────────────┐
│     Pusher      │
│   (Cloud SaaS)  │
└────────┬────────┘
         │
         ├──→ Admin Page (listens to 'rfid')
         │
         └──→ Kiosk Pages (listens to 'rfid-station-{id}')
```

---

## 🎯 Channel Routing

### Admin RFID Management Page
- **Listens to:** `rfid` channel
- **Event:** `card.tapped`
- **Use case:** Assigning RFID cards to users

### Kiosk Pages (Station Check-in)
- **Station 1 listens to:** `rfid-station-1` channel
- **Station 2 listens to:** `rfid-station-2` channel
- **Station 3 listens to:** `rfid-station-3` channel
- **Station 4 listens to:** `rfid-station-4` channel
- **Event:** `card.tapped`
- **Use case:** User check-in at stations

### Registration Desk
- **Listens to:** `rfid-reg-{N}` channel
- **Event:** `card.tapped`
- **Use case:** Registration/enrollment

---

## 🧪 Testing

### Test Admin Page
1. Open Admin → RFID Management
2. Open browser console (F12)
3. Look for: `Pusher connected | channel: rfid`
4. Tap an NFC card
5. Console should show: `Card tapped: [UID]`
6. Modal should auto-fill with UID

### Test Kiosk Page
1. Open `/admin/kiosk/1` (for Station 1)
2. Open browser console (F12)
3. Look for: `Pusher state: connecting → connected`
4. Tap an NFC card
5. Should see check-in process or error message
6. Verify no duplicate scans within 2 seconds

### Test Relay
1. Run `node relay.js` with STATION_ID
2. Console should show:
   ```
   --- NFC Relay (Pusher) ---
   Channel  : rfid-station-1
   Station  : 1
   ```
3. Tap a card on reader
4. Console should show:
   ```
   Card tapped: A1B2C3D4
   Pusher OK  : A1B2C3D4
   ```

---

## 🐛 Troubleshooting

### Issue: "Pusher failed — WebSocket not supported or blocked"

**Cause:** Firewall blocking WebSocket connections or browser doesn't support WebSockets

**Solutions:**
1. Check browser console for specific errors
2. Verify Pusher credentials in `.env`
3. Test Pusher connection: https://pusher.com/docs/channels/getting_started/javascript
4. Check firewall/antivirus settings
5. Try different browser

---

### Issue: Kiosk not receiving card taps

**Cause:** Wrong channel subscription or relay not running

**Solutions:**
1. Verify relay.js is running with correct STATION_ID
2. Check browser console for Pusher connection status
3. Verify channel name matches: `rfid-station-1` not `rfid-station-01`
4. Check Pusher dashboard for live events
5. Ensure BROADCAST_DRIVER=pusher in .env

---

### Issue: Admin page not receiving taps

**Cause:** Relay not configured for admin desk or wrong channel

**Solutions:**
1. Run relay.js WITHOUT STATION_ID or REG_ID
2. Verify admin page subscribes to 'rfid' channel
3. Check Pusher dashboard for events on 'rfid' channel

---

### Issue: "Dedupe — skipping duplicate UID"

**Cause:** Same card tapped within 2 seconds (intentional protection)

**Solution:** This is normal behavior. Wait 2 seconds between taps.

---

### Issue: NFC reader not detected

**Cause:** Driver issue or reader not connected

**Solutions:**
1. **Windows:** Check Device Manager for "Smart Card Reader"
2. **Mac:** System should detect automatically
3. **Linux:** Install `pcscd` service
4. Unplug and replug USB cable
5. Try different USB port
6. Restart computer

---

## 📊 Deployment Scenarios

### Scenario 1: All Readers at VPS
- Run one relay.js instance per reader on VPS
- Each relay pushes directly to Pusher
- Set STATION_ID for each relay

### Scenario 2: Readers at Local Stations
- Run relay.js on each station PC/Mac
- Point to Laravel endpoint via LARAVEL_URL in server.js
- Laravel broadcasts to Pusher

### Scenario 3: Hybrid (Recommended)
- Admin desk reader → relay.js → Pusher (direct)
- Station readers → Keyboard wedge (simple, reliable)
- Use NFC only for admin desk, keyboard wedge for kiosks

---

## 🔒 Security Notes

1. **Never commit Pusher secrets to Git**
   - Add `.env` to `.gitignore`
   - Use environment variables

2. **Protect /rfid/receive endpoint**
   - Already protected with X-RFID-Token header
   - Keep RFID_TOKEN secret

3. **Monitor Pusher usage**
   - Free tier: 200k messages/day
   - Check dashboard regularly

---

## 📈 Monitoring

### Pusher Dashboard
https://dashboard.pusher.com

**Monitor:**
- Connection count
- Message volume
- Error rate
- Active channels

### Laravel Logs
```bash
tail -f storage/logs/laravel.log | grep -i rfid
```

### Browser Console
Press F12 in any page to see:
- Pusher connection status
- Channel subscriptions
- Received events
- Errors

---

## 💰 Cost Estimation

### Pusher Free Tier
- 200,000 messages/day
- 100 concurrent connections
- Unlimited channels

**Estimated Daily Usage:**
- 4 stations × 100 scans/day = 400 messages
- 1 admin × 50 scans/day = 50 messages
- **Total: ~500 messages/day** ✅ Well within free tier

### Paid Plans (if needed)
- $49/month: 1M messages/day
- Custom: Higher volumes

---

## 🎓 Advanced Configuration

### Custom Pusher Cluster
If your server is in Asia, use AP cluster:

```bash
PUSHER_APP_CLUSTER=ap1
```

### Custom Dedupe Time
Edit relay.js:
```javascript
const DEDUPE_MS = 3000; // 3 seconds instead of 2
```

### Multiple Relays on Same Machine
Use PM2 ecosystem file:

```javascript
// ecosystem.config.js
module.exports = {
  apps: [
    {
      name: 'rfid-station-1',
      script: 'relay.js',
      env: { STATION_ID: '1' }
    },
    {
      name: 'rfid-station-2',
      script: 'relay.js',
      env: { STATION_ID: '2' }
    }
  ]
};
```

Start all:
```bash
pm2 start ecosystem.config.js
```

---

## ✅ Final Checklist

- [ ] Pusher credentials configured in `.env`
- [ ] BROADCAST_DRIVER=pusher in `.env`
- [ ] `composer require pusher/pusher-php-server` installed
- [ ] relay.js dependencies installed (`npm install` in rfid-relay/)
- [ ] NFC reader connected and detected
- [ ] relay.js running with correct STATION_ID
- [ ] Admin page connects to Pusher
- [ ] Kiosk pages connect to Pusher
- [ ] Card taps appear in browser console
- [ ] Check-in works successfully
- [ ] PM2 configured for production (optional)

---

## 📞 Support

### If NFC still doesn't work:

1. **Check browser console** (F12) for exact error messages
2. **Check Laravel logs** in `storage/logs/laravel.log`
3. **Check Pusher dashboard** for connection issues
4. **Verify environment variables** are set correctly
5. **Test with a simple keyboard-wedge reader** to isolate hardware issues

### Quick Test Command
```bash
# Test if Pusher credentials work
cd rfid-relay
node -e "
const Pusher = require('pusher');
const p = new Pusher({
  appId: '2147496',
  key: '8e90c33567599ca7143e',
  secret: 'fb29453503976eb53bcd',
  cluster: 'us2'
});
p.trigger('test', 'test-event', {msg: 'hello'})
  .then(() => console.log('✅ Pusher works!'))
  .catch(err => console.error('❌ Pusher error:', err));
"
```

---

**Last Updated:** 2026-08-24  
**System Version:** Pusher-based NFC Implementation  
**Status:** ✅ Production Ready
