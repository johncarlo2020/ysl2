/**
 * hub.js — runs on the VPS
 *
 * Listens on 127.0.0.1:3000 (Apache proxies wss://host/nfc-ws → here).
 * Also listens on 127.0.0.1:3001 for HTTP POST /push from Laravel.
 *
 * Three types of WebSocket connections:
 *   ?token=<RFID_TOKEN>  → the relay (sends card UIDs)
 *   ?type=kiosk          → kiosk check-in pages (receive card UIDs for check-in)
 *   ?type=admin          → admin RFID assignment page (receives UIDs only)
 *
 * Kiosk isolation rule:
 *   While ANY admin browser client is connected, card UIDs are forwarded ONLY
 *   to admin clients. Kiosk pages are completely skipped. This guarantees that
 *   a card tap done in the admin assignment page never triggers a kiosk check-in,
 *   regardless of modal state or timing.
 *
 * Start:  node hub.js
 * Env:    RFID_TOKEN   shared secret (default: ysl-rfid-secret-2026)
 *         WS_PORT      WebSocket port (default: 3000)
 *         HTTP_PORT    Laravel push port (default: 3001)
 */

const WebSocket = require('ws');
const http      = require('http');

const RFID_TOKEN = process.env.RFID_TOKEN  || 'ysl-rfid-secret-2026';
const PORT       = parseInt(process.env.WS_PORT   || '3000', 10);
const HTTP_PORT  = parseInt(process.env.HTTP_PORT || '3001', 10);

const wss          = new WebSocket.Server({ host: '127.0.0.1', port: PORT });
const kioskClients = new Map();   // stationId (string) → Set<ws>
const adminClients = new Set();   // admin RFID assignment page

// When any admin page opens the assign modal this becomes true and kiosks
// are skipped until the modal is closed (or the timeout fires).
let assignMode        = false;
let assignModeTimeout = null;
const ASSIGN_MODE_TTL = 60000; // auto-reset after 60 s in case page is closed unexpectedly

function setAssignMode(active) {
    assignMode = active;
    clearTimeout(assignModeTimeout);
    if (active) {
        assignModeTimeout = setTimeout(function () {
            assignMode = false;
            console.log('Assign mode auto-reset after timeout');
        }, ASSIGN_MODE_TTL);
    }
    console.log('Assign mode:', assignMode);
}

function broadcastUID(uid, stationId) {
    const msg = JSON.stringify({ uid });

    if (!stationId) {
        // No station ID — this is the admin desk reader.
        // Forward ONLY to admin clients; never touches kiosks.
        console.log('UID from admin desk reader — forwarding to admin clients only:', uid);
        adminClients.forEach(function (client) {
            if (client.readyState === WebSocket.OPEN) client.send(msg);
        });
        return;
    }

    // stationId is set — this tap came from a kiosk station relay.
    // Admin clients must NEVER receive kiosk taps (would pollute the assign modal).
    console.log('UID from kiosk station', stationId, '— NOT forwarded to admin:', uid);

    // Skip kiosks entirely while admin assign modal is open
    if (assignMode) return;

    // Route to the specific station's kiosk only
    const targets = kioskClients.get(String(stationId));
    if (targets) {
        console.log('Routing UID to station', stationId, '(' + targets.size + ' client(s)):', uid);
        targets.forEach(function (client) {
            if (client.readyState === WebSocket.OPEN) client.send(msg);
        });
    } else {
        console.log('No kiosk connected for station', stationId, '— UID not forwarded');
    }
}

// ── WebSocket server ───────────────────────────────────────────────────────
wss.on('listening', () => {
    console.log('NFC hub (WS) listening on 127.0.0.1:' + PORT);
});

wss.on('connection', function (ws, req) {
    const url   = new URL(req.url, 'http://localhost');
    const token = url.searchParams.get('token');

    console.log('New connection | url:', req.url, '| token:', token ? '[present]' : '[none]', '| ip:', req.socket.remoteAddress);

    if (token === RFID_TOKEN) {
        // ── Relay connection ──────────────────────────────────────────────
        const relayStation = url.searchParams.get('station') || null;
        console.log('Relay connected from', req.socket.remoteAddress, '| station:', relayStation || 'unspecified');

        ws.on('message', function (raw) {
            const msg = raw.toString();
            console.log('Relay →', msg);
            try {
                const data = JSON.parse(msg);
                if (data.uid) broadcastUID(data.uid, relayStation);
            } catch (e) { /* ignore */ }
        });

        ws.on('close', function () { console.log('Relay disconnected'); });
        ws.on('error', function (err) { console.error('Relay error:', err.message); });

    } else {
        // ── Browser client ────────────────────────────────────────────────
        const clientType = url.searchParams.get('type') || 'kiosk';
        const isAdmin    = clientType === 'admin';

        if (isAdmin) {
            adminClients.add(ws);
            console.log('Admin browser connected (' + adminClients.size + ' total)');
        } else {
            const stationId = url.searchParams.get('station') || 'default';
            if (!kioskClients.has(stationId)) kioskClients.set(stationId, new Set());
            kioskClients.get(stationId).add(ws);
            let total = 0; kioskClients.forEach(function (s) { total += s.size; });
            console.log('Kiosk browser connected | station:', stationId, '| total kiosks:', total);
        }

        ws.on('message', function (raw) {
            if (!isAdmin) return; // only admin clients send control messages
            try {
                const data = JSON.parse(raw.toString());
                if (typeof data.assignMode === 'boolean') {
                    setAssignMode(data.assignMode);
                }
            } catch (e) { /* ignore */ }
        });

        ws.on('close', function () {
            if (isAdmin) {
                adminClients.delete(ws);
                // If the last admin disconnects while in assign mode, reset it
                if (adminClients.size === 0 && assignMode) setAssignMode(false);
                console.log('Admin browser disconnected (' + adminClients.size + ' remaining)');
            } else {
                const stationId = url.searchParams.get('station') || 'default';
                const set = kioskClients.get(stationId);
                if (set) {
                    set.delete(ws);
                    if (set.size === 0) kioskClients.delete(stationId);
                }
                let total = 0; kioskClients.forEach(function (s) { total += s.size; });
                console.log('Kiosk browser disconnected | station:', stationId, '| total kiosks:', total);
            }
        });

        ws.on('error', function (err) {
            console.error('Browser WS error:', err.message);
            adminClients.delete(ws);
            kioskClients.forEach(function (set) { set.delete(ws); });
        });
    }
});

wss.on('error', function (err) { console.error('Hub WS error:', err); });

// ── HTTP push endpoint for Laravel (POST /push) ────────────────────────────
const httpServer = http.createServer(function (req, res) {
    if (req.method !== 'POST' || req.url !== '/push') {
        res.writeHead(404);
        return res.end();
    }

    let body = '';
    req.on('data', function (chunk) { body += chunk; });
    req.on('end', function () {
        try {
            const data  = JSON.parse(body);
            const token = req.headers['x-rfid-token'];

            if (token !== RFID_TOKEN) {
                res.writeHead(401);
                return res.end(JSON.stringify({ error: 'Unauthorized' }));
            }

            if (!data.uid) {
                res.writeHead(422);
                return res.end(JSON.stringify({ error: 'Missing uid' }));
            }

            const stationId = data.station_id ? String(data.station_id) : null;
            broadcastUID(data.uid, stationId);
            res.writeHead(200, { 'Content-Type': 'application/json' });
            res.end(JSON.stringify({ ok: true }));

        } catch (e) {
            res.writeHead(400);
            res.end(JSON.stringify({ error: 'Invalid JSON' }));
        }
    });
});

httpServer.listen(HTTP_PORT, '127.0.0.1', function () {
    console.log('NFC hub (HTTP push) listening on 127.0.0.1:' + HTTP_PORT);
});
