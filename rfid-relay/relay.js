/**
 * relay.js — runs on the Mac (where the ACR122U is plugged in)
 *
 * Reads NFC card UIDs and pushes them directly to Pusher.
 *
 * Start:  node relay.js
 * Env:
 *   PUSHER_APP_ID      Pusher app ID        (default: 2147496)
 *   PUSHER_APP_KEY     Pusher app key       (default: 8e90c33567599ca7143e)
 *   PUSHER_APP_SECRET  Pusher app secret    (default: fb29453503976eb53bcd)
 *   PUSHER_APP_CLUSTER Pusher cluster       (default: us2)
 *   STATION_ID         kiosk station number — pushes to rfid-station-{N} channel  e.g. STATION_ID=1
 *   REG_ID             registration desk   — pushes to rfid-reg-{N} channel       e.g. REG_ID=1
 *                      Use either STATION_ID or REG_ID, not both.
 */

const { NFC } = require('nfc-pcsc');
const Pusher  = require('pusher');

const PUSHER_APP_ID      = process.env.PUSHER_APP_ID      || '2147496';
const PUSHER_APP_KEY     = process.env.PUSHER_APP_KEY     || '8e90c33567599ca7143e';
const PUSHER_APP_SECRET  = process.env.PUSHER_APP_SECRET  || 'fb29453503976eb53bcd';
const PUSHER_APP_CLUSTER = process.env.PUSHER_APP_CLUSTER || 'us2';
const STATION_ID         = process.env.STATION_ID         || '';
const REG_ID             = process.env.REG_ID             || '';

// Determine the Pusher channel to publish on
function getChannel() {
    if (STATION_ID) return 'rfid-station-' + STATION_ID;
    if (REG_ID)     return 'rfid-reg-'     + REG_ID;
    return 'rfid';
}

const CHANNEL = getChannel();

const pusher = new Pusher({
    appId:   PUSHER_APP_ID,
    key:     PUSHER_APP_KEY,
    secret:  PUSHER_APP_SECRET,
    cluster: PUSHER_APP_CLUSTER,
    useTLS:  true,
});

console.log('--- NFC Relay (Pusher) ---');
console.log('Channel  :', CHANNEL);
console.log('Station  :', STATION_ID || '(unspecified)');
console.log('Reg      :', REG_ID     || '(not a reg relay)');

// ── Dedupe — ignore the same UID within DEDUPE_MS milliseconds ─────────────
const DEDUPE_MS  = 2000;
let lastUid      = null;
let lastUidTime  = 0;

function isDuplicate(uid) {
    const now = Date.now();
    if (uid === lastUid && (now - lastUidTime) < DEDUPE_MS) {
        return true;
    }
    lastUid     = uid;
    lastUidTime = now;
    return false;
}
// ──────────────────────────────────────────────────────────────────────────

// ── Pusher trigger ─────────────────────────────────────────────────────────
function sendToPusher(uid) {
    if (isDuplicate(uid)) {
        console.log('Dedupe — skipping duplicate UID:', uid);
        return;
    }

    const data = { uid };
    if (STATION_ID) data.station_id = STATION_ID;
    if (REG_ID)     data.reg_id     = REG_ID;

    pusher.trigger(CHANNEL, 'card.tapped', data)
        .then(function ()    { console.log('Pusher OK  :', uid); })
        .catch(function (err) { console.error('Pusher error:', err.message); });
}
// ──────────────────────────────────────────────────────────────────────────

// ── NFC reader ─────────────────────────────────────────────────────────────
const nfc = new NFC();
let readerConnected = false;
let readerName = null;

nfc.on('reader', function (reader) {
    console.log('Reader connected:', reader.name);
    readerConnected = true;
    readerName = reader.name;
    broadcastReaderStatus();

    reader.on('card', function (card) {
        const uid = card.uid.toUpperCase();
        console.log('Card tapped:', uid);
        sendToPusher(uid);
    });

    reader.on('error', function (err) { console.error('Reader error:', err); });
    reader.on('end',   function ()    { 
        console.log('Reader removed:', reader.name);
        readerConnected = false;
        readerName = null;
        broadcastReaderStatus();
    });
});

nfc.on('error', function (err) { console.error('NFC error:', err); });

// ── Broadcast reader status every 5 seconds ───────────────────────────────
function broadcastReaderStatus() {
    const statusData = {
        connected: readerConnected,
        reader: readerName,
        timestamp: Date.now()
    };
    if (STATION_ID) statusData.station_id = STATION_ID;
    if (REG_ID)     statusData.reg_id     = REG_ID;

    pusher.trigger(CHANNEL, 'reader.status', statusData)
        .then(() => console.log('Status broadcast:', readerConnected ? 'Connected' : 'Disconnected'))
        .catch(err => console.error('Status broadcast error:', err.message));
}

// Send status immediately on start
broadcastReaderStatus();

// Then every 5 seconds
setInterval(broadcastReaderStatus, 5000);
// ──────────────────────────────────────────────────────────────────────────
