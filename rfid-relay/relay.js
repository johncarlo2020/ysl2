/**
 * relay.js — runs on the Mac (where the ACR122U is plugged in)
 *
 * Reads NFC card UIDs and:
 *   1. Sends them to the VPS hub via WebSocket  (wss://host/nfc-ws?token=...)
 *   2. HTTP POSTs them to Laravel               (optional, for server-side logging)
 *
 * Start:  node relay.js
 * Env:    HUB_URL      WebSocket hub URL   (default: wss://nfctest.wowbynow.com.my/nfc-ws)
 *         LARAVEL_URL  Laravel endpoint     (default: https://nfctest.wowbynow.com.my/rfid/receive)
 *         RFID_TOKEN   shared secret        (default: ysl-rfid-secret-2026)
 */

const { NFC }  = require('nfc-pcsc');
const https    = require('https');
const http     = require('http');
const WebSocket = require('ws');

const HUB_URL     = process.env.HUB_URL     || 'wss://nfctest.wowbynow.com.my/nfc-ws';
const LARAVEL_URL = process.env.LARAVEL_URL || 'https://nfctest.wowbynow.com.my/rfid/receive';
const RFID_TOKEN  = process.env.RFID_TOKEN  || 'ysl-rfid-secret-2026';

console.log('--- NFC Relay (Mac) ---');
console.log('Hub    :', HUB_URL);
console.log('Laravel:', LARAVEL_URL);

// ── WebSocket connection to VPS hub ────────────────────────────────────────
let ws = null;

function connectHub() {
    const url = HUB_URL + '?token=' + encodeURIComponent(RFID_TOKEN);
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
    const body = JSON.stringify({ uid });
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
