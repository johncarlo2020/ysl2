/**
 * relay.js — runs on the Mac (where the ACR122U is plugged in)
 *
 * Reads NFC card UIDs and:
 *   1. Sends them to the VPS hub via WebSocket  (wss://host/nfc-ws?token=...)
 *   2. HTTP POSTs them to Laravel               (optional, for server-side logging)
 *
 * Start:  node relay.js
 *         HUB_URL      WebSocket hub URL   (default: wss://sg.lovenudebeautyhotel.com/nfc-ws)
 *         LARAVEL_URL  Laravel endpoint     (default: https://sg.lovenudebeautyhotel.com/rfid/receive)
 *         RFID_TOKEN   shared secret        (default: ysl-rfid-secret-2026)
 *         STATION_ID   kiosk station number — connects as kiosk relay    e.g. STATION_ID=1
 *         REG_ID       registration desk number — connects as admin/reg relay  e.g. REG_ID=1
 *                      Use either STATION_ID or REG_ID, not both.
 */

const { NFC }  = require('nfc-pcsc');
const https    = require('https');
const http     = require('http');
const WebSocket = require('ws');

const HUB_URL     = process.env.HUB_URL     || 'wss://sg.lovenudebeautyhotel.com/nfc-ws';
const LARAVEL_URL = process.env.LARAVEL_URL || 'https://sg.lovenudebeautyhotel.com/rfid/receive';
const RFID_TOKEN  = process.env.RFID_TOKEN  || 'ysl-rfid-secret-2026';
const STATION_ID  = process.env.STATION_ID  || '';   // e.g. STATION_ID=1 node relay.js
const REG_ID      = process.env.REG_ID      || '';   // e.g. REG_ID=1 node relay.js  (admin/reg desk)

console.log('--- NFC Relay (Mac) ---');
console.log('Hub    :', HUB_URL);
console.log('Laravel:', LARAVEL_URL);
console.log('Station:', STATION_ID || '(unspecified)');
console.log('Reg    :', REG_ID     || '(not a reg relay)');

// ── WebSocket connection to VPS hub ────────────────────────────────────────
let ws = null;

function connectHub() {
    const url = HUB_URL + '?token=' + encodeURIComponent(RFID_TOKEN) +
                (STATION_ID ? '&station=' + encodeURIComponent(STATION_ID) : '') +
                (REG_ID     ? '&reg='     + encodeURIComponent(REG_ID)     : '');
    console.log('Connecting to hub…');
    ws = new WebSocket(url);

    ws.on('open',  function ()    { console.log('Hub connected'); });
    ws.on('error', function (err) { console.error('Hub error:', err.message); });
    ws.on('close', function ()    {
        console.log('Hub disconnected — retrying in 5 s');
        ws = null;
        setTimeout(connectHub, 5000);
    });
}

connectHub();

function sendToHub(uid) {
    if (ws && ws.readyState === WebSocket.OPEN) {
        ws.send(JSON.stringify({ uid }));
    } else {
        console.warn('Hub not connected — UID not forwarded:', uid);
    }
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
    reader.on('end',   function ()    { console.log('Reader removed:', reader.name); });
});

nfc.on('error', function (err) { console.error('NFC error:', err); });
// ──────────────────────────────────────────────────────────────────────────

function postToLaravel(uid) {
    const payload = STATION_ID ? { uid, station_id: STATION_ID } : { uid };
    const body = JSON.stringify(payload);
    const url  = new URL(LARAVEL_URL);
    const lib  = url.protocol === 'https:' ? https : http;

    const options = {
        hostname : url.hostname,
        port     : url.port || (url.protocol === 'https:' ? 443 : 80),
        path     : url.pathname,
        method   : 'POST',
        headers  : {
            'Content-Type'  : 'application/json',
            'Content-Length': Buffer.byteLength(body),
            'X-RFID-Token'  : RFID_TOKEN,
        },
    };

    const req = lib.request(options, function (res) {
        let data = '';
        res.on('data', function (chunk) { data += chunk; });
        res.on('end',  function ()      { console.log('Laravel [' + res.statusCode + ']:', data); });
    });

    req.on('error', function (err) { console.error('HTTP error:', err.message); });
    req.write(body);
    req.end();
}
