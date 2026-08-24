const { NFC } = require('nfc-pcsc');
const https = require('https');
const http = require('http');
const WebSocket = require('ws');

const LARAVEL_URL = process.env.LARAVEL_URL || 'http://localhost/rfid/receive';
const LARAVEL_STATUS_URL = process.env.LARAVEL_STATUS_URL || 'http://localhost/rfid/status';
const RFID_TOKEN  = process.env.RFID_TOKEN  || 'ysl-rfid-secret-2026';
const WS_PORT     = process.env.WS_PORT     || 3000;
const STATION_ID  = process.env.STATION_ID  || '';
const REG_ID      = process.env.REG_ID      || '';

// ── Local WebSocket server ─────────────────────────────────────────────────
const wss = new WebSocket.Server({ host: '127.0.0.1', port: WS_PORT });
wss.on('listening', () => console.log('WebSocket server listening on 127.0.0.1:' + WS_PORT));
wss.on('error',     (err) => console.error('WebSocket server error:', err));

function broadcastUID(uid) {
    const msg = JSON.stringify({ uid });
    wss.clients.forEach(function (client) {
        if (client.readyState === WebSocket.OPEN) {
            client.send(msg);
        }
    });
}
// ──────────────────────────────────────────────────────────────────────────

console.log('--- NFC Relay ---');
console.log('POSTing card UIDs to:', LARAVEL_URL);
console.log('POSTing reader status to:', LARAVEL_STATUS_URL);
console.log('Station ID:', STATION_ID || '(none)');
console.log('Reg ID:', REG_ID || '(none)');

const nfc = new NFC();
let readerConnected = false;
let readerName = null;

nfc.on('reader', function (reader) {
    console.log('Reader connected:', reader.name);
    readerConnected = true;
    readerName = reader.name;
    postReaderStatus();

    reader.on('card', function (card) {
        const uid = card.uid.toUpperCase();
        console.log('Card tapped:', uid);
        broadcastUID(uid);
        postToLaravel(uid);
    });

    reader.on('error', function (err) { console.error('Reader error:', err); });
    reader.on('end',   function ()    { 
        console.log('Reader removed:', reader.name);
        readerConnected = false;
        readerName = null;
        postReaderStatus();
    });
});

nfc.on('error', function (err) { console.error('NFC error:', err); });

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

function postReaderStatus() {
    const statusData = {
        connected: readerConnected,
        reader: readerName,
        timestamp: Date.now()
    };
    if (STATION_ID) statusData.station_id = STATION_ID;
    if (REG_ID) statusData.reg_id = REG_ID;

    const body = JSON.stringify(statusData);
    const url  = new URL(LARAVEL_STATUS_URL);
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
        res.on('end',  function () { 
            console.log('Status posted [' + res.statusCode + ']:', readerConnected ? 'Connected' : 'Disconnected');
        });
    });

    req.on('error', function (err) { console.error('Status POST error:', err.message); });
    req.write(body);
    req.end();
}

// Send status immediately on start
postReaderStatus();

// Then every 5 seconds
setInterval(postReaderStatus, 5000);


