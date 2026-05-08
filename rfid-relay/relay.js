/**
 * relay.js — runs on the Mac (where the ACR122U is plugged in)
 *
 * Reads NFC card UIDs and:
 *   1. Triggers a Pusher event (no persistent connection — immune to Cloudflare timeouts)
 *   2. HTTP POSTs them to Laravel  (optional, for server-side logging)
 *
 * Start:  node relay.js
 *         PUSHER_APP_ID      Pusher app ID
 *         PUSHER_APP_KEY     Pusher app key
 *         PUSHER_APP_SECRET  Pusher app secret
 *         PUSHER_APP_CLUSTER Pusher cluster   (default: mt1)
 *         LARAVEL_URL        Laravel endpoint (default: https://my.lovenudebeautyhotel.com/rfid/receive)
 *         STATION_ID         e.g. STATION_ID=1 node relay.js
 */

const { NFC } = require('nfc-pcsc');
const https = require('https');
const http = require('http');
const Pusher = require('pusher');

const LARAVEL_URL = process.env.LARAVEL_URL || 'https://my.lovenudebeautyhotel.com/rfid/receive';
const STATION_ID = process.env.STATION_ID || '';   // e.g. STATION_ID=1 node relay.js

const pusher = new Pusher({
    appId: process.env.PUSHER_APP_ID,
    key: process.env.PUSHER_APP_KEY,
    secret: process.env.PUSHER_APP_SECRET,
    cluster: process.env.PUSHER_APP_CLUSTER || 'mt1',
    useTLS: true,
});

console.log('--- NFC Relay (Mac) ---');
console.log('Laravel:', LARAVEL_URL);
console.log('Station:', STATION_ID || '(unspecified)');
console.log('Pusher cluster:', process.env.PUSHER_APP_CLUSTER || 'mt1');

// ── Send UID via Pusher (HTTP POST to Pusher — no persistent connection) ───
function sendToHub(uid) {
    const channel = STATION_ID ? ('nfc-station-' + STATION_ID) : 'nfc-admin';
    pusher.trigger(channel, 'card.tapped', { uid })
        .then(function () { console.log('Pusher: sent to', channel, '→', uid); })
        .catch(function (err) { console.error('Pusher error:', err.message); });
}
// ──────────────────────────────────────────────────────────────────────────

// ── NFC reader ─────────────────────────────────────────────────────────────
const nfc = new NFC();

nfc.on('reader', function (reader) {
    console.log('Reader connected:', reader.name);

    reader.on('card', function (card) {
        const uid = card.uid.toUpperCase();
        console.log('Card tapped:', uid);
        sendToHub(uid);
        postToLaravel(uid);
    });

    reader.on('error', function (err) { console.error('Reader error:', err); });
    reader.on('end', function () { console.log('Reader removed:', reader.name); });
});

nfc.on('error', function (err) { console.error('NFC error:', err); });
// ──────────────────────────────────────────────────────────────────────────

function postToLaravel(uid) {
    const payload = STATION_ID ? { uid, station_id: STATION_ID } : { uid };
    const body = JSON.stringify(payload);
    const url = new URL(LARAVEL_URL);
    const lib = url.protocol === 'https:' ? https : http;

    const options = {
        hostname: url.hostname,
        port: url.port || (url.protocol === 'https:' ? 443 : 80),
        path: url.pathname,
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Content-Length': Buffer.byteLength(body),
        },
    };

    const req = lib.request(options, function (res) {
        let data = '';
        res.on('data', function (chunk) { data += chunk; });
        res.on('end', function () { console.log('Laravel [' + res.statusCode + ']:', data); });
    });

    req.on('error', function (err) { console.error('HTTP error:', err.message); });
    req.write(body);
    req.end();
}
