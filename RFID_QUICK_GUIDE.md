# RFID System - Quick Reference Guide

## 🚀 Quick Start

### For Station Kiosks
1. Open the kiosk page on the station device
2. Check connection status (green dot = ready)
3. Users tap their RFID card
4. System auto-validates and checks them in

### For Admin Card Assignment
1. Go to Admin → RFID Management
2. Click "Assign" next to user
3. Tap RFID card on reader
4. Card auto-fills and saves
5. Modal closes on success

---

## ⚡ System Status

### Connection Indicators
- **Green dot + "Connected"** = System ready
- **Red blinking dot + "Reconnecting..."** = Connection issue
- **Check:** Ensure hub.js is running on VPS

### WebSocket Hub Status
```bash
# On VPS, check if hub.js is running
ps aux | grep hub.js

# If not running, start it
node /path/to/rfid-relay/hub.js

# Or use PM2 (recommended)
pm2 start hub.js --name rfid-hub
pm2 save
```

---

## 🔒 Security Features

### Rate Limits (per 60 seconds)
- **Kiosk check-in:** 10 scans per card
- **Admin assignment:** 5 scans per session
- **Card checking:** 10 checks per session
- **Per IP:** 20 total scans

### Cooldown Periods
- **Kiosk:** 2 seconds between scans
- **Admin:** 1 second between scans

**If you see "Too many attempts":** Wait 60 seconds before trying again.

---

## ⚙️ Keyboard-Wedge Settings

### Optimal Reader Settings
- **Read mode:** Keyboard wedge
- **Suffix:** Enter key (CR)
- **Prefix:** None
- **Format:** Hexadecimal uppercase
- **Delay:** 0ms between characters

### Supported UID Formats
- ✅ `A1B2C3D4`
- ✅ `A1:B2:C3:D4`
- ✅ `A1-B2-C3-D4`
- ✅ Length: 6-64 characters
- ❌ Test patterns: `0000000000`, `AAAAAAAAAA`

---

## 🐛 Common Issues & Solutions

### Issue: Card not recognized
**Symptoms:** "Card not assigned to any user" error  
**Solutions:**
1. Verify card is assigned in Admin → RFID Management
2. Check if UID is correct (use "Check Card" button)
3. Ensure UID is between 6-64 characters

---

### Issue: "Already checked in"
**Symptoms:** Duplicate check-in message  
**Solutions:**
- This is normal – user already checked in at this station
- Check in at a different station or complete the activity

---

### Issue: "Complete stations 1, 2 & 3 first"
**Symptoms:** Station 4 check-in blocked  
**Solutions:**
- Station 4 requires prerequisites
- User must visit stations 1, 2, and 3 first
- Check user's progress in Admin panel

---

### Issue: "Too many attempts"
**Symptoms:** Rate limit reached  
**Solutions:**
- Wait 60 seconds before trying again
- Don't scan the same card repeatedly
- Contact admin if problem persists

---

### Issue: Connection status red/disconnected
**Symptoms:** Red blinking dot, "Reconnecting..." message  
**Solutions:**
1. Check if hub.js is running on VPS
2. Verify network connectivity
3. Check WebSocket port (3000) is accessible
4. Review Apache/Nginx WebSocket proxy config

---

### Issue: Scans not registering
**Symptoms:** Nothing happens when card is tapped  
**Solutions:**
1. Ensure kiosk page has focus (click on the page)
2. Check reader is in keyboard-wedge mode
3. Verify reader is connected via USB
4. Look for JavaScript errors in browser console (F12)
5. Check if cooldown period is active (2 seconds)

---

### Issue: Wrong card assigned to user
**Symptoms:** User A has User B's card  
**Solutions:**
1. Go to Admin → RFID Management
2. Click "Unlink" on the incorrect assignment
3. Click "Assign" on the correct user
4. Tap the card to reassign

---

## 📊 Monitoring

### View Activity Logs
Go to Admin → RFID Logs (if implemented) or check database:
```sql
SELECT * FROM rfid_logs 
ORDER BY created_at DESC 
LIMIT 100;
```

### Check Suspicious Activity
```sql
-- Failed attempts (3+ in 15 minutes)
SELECT rfid_uid, COUNT(*) as fails
FROM rfid_logs
WHERE status = 'error' 
  AND created_at >= NOW() - INTERVAL 15 MINUTE
GROUP BY rfid_uid
HAVING fails >= 3;

-- Rapid scans (10+ in 15 minutes)
SELECT ip_address, COUNT(*) as scans
FROM rfid_logs
WHERE created_at >= NOW() - INTERVAL 15 MINUTE
GROUP BY ip_address
HAVING scans >= 10;
```

---

## 🔧 Admin Tasks

### Assigning RFID Cards
1. Navigate to Admin → RFID Management
2. Find user in the table (use search)
3. Click "Assign" button
4. Modal opens – system blocks kiosk scans
5. Tap RFID card on admin desk reader
6. UID auto-fills in input field
7. Click "Save" or press Enter
8. Modal closes – kiosk scans resume

### Unlinking RFID Cards
1. Navigate to Admin → RFID Management
2. Find user with assigned card
3. Click "Unlink" button
4. Confirm the action
5. Card is removed from user

### Checking Card Ownership
1. Navigate to Admin → RFID Management
2. Click "Check Card" button (top right)
3. Tap RFID card or type UID
4. System shows if card is linked to a user
5. Option to unlink directly from modal

---

## 📱 Station Kiosk Pages

### Opening Kiosk Pages
From Admin → RFID Management page, click station links:
- **Station 1:** Opens kiosk for Station 1
- **Station 2:** Opens kiosk for Station 2
- etc.

**Best Practice:** Open each kiosk on its dedicated device and keep the tab open 24/7.

### Kiosk Display
- Station name and ID at top
- Connection status indicator (top right)
- Large card icon
- "Tap your RFID card" instruction
- Status badge (shows success/error)
- Footer hint (keep tab open reminder)

---

## 🎯 Performance Tips

### For Best Performance
1. **Keep hub.js running** continuously on VPS
2. **Use modern browsers** (Chrome, Edge, Firefox)
3. **Hardwired network** preferred over WiFi
4. **USB 2.0+ ports** for RFID readers
5. **Close unused browser tabs** on kiosk devices

### Cleanup Old Logs
Run this monthly to prevent database bloat:
```sql
DELETE FROM rfid_logs 
WHERE created_at < NOW() - INTERVAL 90 DAY;
```

Or use Laravel:
```php
\App\Models\RfidLog::where('created_at', '<', now()->subDays(90))->delete();
```

---

## 📞 Emergency Contacts

### System Down
1. Check hub.js process on VPS
2. Check Laravel logs: `storage/logs/laravel.log`
3. Check Apache/Nginx error logs
4. Restart hub.js if needed

### Reader Not Working
1. Unplug and replug USB cable
2. Check Windows Device Manager for reader
3. Test reader in Notepad (should type UID on scan)
4. Replace reader if faulty

### Database Issues
1. Check MySQL service is running
2. Run migrations: `php artisan migrate`
3. Check disk space on server
4. Review `rfid_logs` table size

---

## 📚 Related Documentation
- [RFID_SECURITY_IMPROVEMENTS.md](RFID_SECURITY_IMPROVEMENTS.md) - Full security details
- [hub.js](rfid-relay/hub.js) - WebSocket hub configuration
- Laravel routes: [routes/web.php](routes/web.php)
- Controller: [app/Http/Controllers/StationController.php](app/Http/Controllers/StationController.php)

---

**Quick Help:** For immediate assistance, check browser console (F12) for error messages.

**Last Updated:** 2026-08-24
