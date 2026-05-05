/**
 * hub.js — runs on the VPS
 *
 * Listens on 127.0.0.1:3002 (Apache proxies wss://sg.lovenudebeautyhotel.com/nfc-ws → here).
 * Also listens on 127.0.0.1:3003 for HTTP POST /push from Laravel.
 *
 * Three types of WebSocket connections:
 *   ?token=<RFID_TOKEN>&station=N  → kiosk relay for station N (sends card UIDs)
 *   ?token=<RFID_TOKEN>&reg=N      → registration desk relay N (sends card UIDs)
 *   ?type=kiosk&station=N          → kiosk check-in page for station N (receives UIDs)
 *   ?type=admin&reg=N              → admin assignment page for reg desk N (receives UIDs only)
 *
 * Kiosk isolation rule:
 *   While ANY admin browser client is connected, card UIDs are forwarded ONLY
 *   to admin clients. Kiosk pages are completely skipped. This guarantees that
 *   a card tap done in the admin assignment page never triggers a kiosk check-in,
 *   regardless of modal state or timing.
 *
 * Start:  node hub.js
 * Env:    RFID_TOKEN   shared secret (default: ysl-rfid-secret-2026)
 *         WS_PORT      WebSocket port (default: 3002)
 *         HTTP_PORT    Laravel push port (default: 3003)
 */

const WebSocket = require('ws');
const http      = require('http');

const RFID_TOKEN = process.env.RFID_TOKEN  || 'ysl-rfid-secret-2026';
const PORT       = parseInt(process.env.WS_PORT   || '3002', 10);
const HTTP_PORT  = parseInt(process.env.HTTP_PORT || '3003', 10);

const wss          = new WebSocket.Server({ host: '127.0.0.1', port: PORT });
const kioskClients = new Map();   // stationId (string) → Set<ws>
const adminClients = new Map();   // regId (string) → Set<ws>  — registration/admin desks

const ASSIGN_MODE_TTL = 60000; // auto-reset after 60 s if page is closed unexpectedly
const assignModes     = new Map();  // regId (string) → timeout handle (present = active)

function setAssignMode(active, regId) {
    const key = String(regId || 'default');
    clearTimeout(assignModes.get(key));
    if (active) {
        const t = setTimeout(function () {
            assignModes.delete(key);
            console.log('Assign mode auto-reset for reg:', key);
        }, ASSIGN_MODE_TTL);
        assignModes.set(key, t);
    } else {
        assignModes.delete(key);
    }
    console.log('Assign mode:', active, '| reg:', key);
}

function isAnyAssignModeActive() {
    return assignModes.size > 0;
}

function broadcastUID(uid, stationId, regId) {
    const msg = JSON.stringify({ uid });

    if (regId) {
        // Reg/admin relay tap — forward only to admin clients for this reg desk.
        const key     = String(regId);
        const clients = adminClients.get(key);
        console.log('UID from reg relay', key, '— forwarding to admin clients for reg:', uid);
        if (clients && clients.size > 0) {
            clients.forEach(function (client) {
                if (client.readyState === WebSocket.OPEN) client.send(msg);
            });
        } else {
            console.log('No admin clients connected for reg', key);
        }
        return;
    }

    if (!stationId) {
        // Legacy: unidentified relay — forward to ALL admin clients.
        console.log('UID from unidentified relay — forwarding to all admin clients:', uid);
        adminClients.forEach(function (clients) {
            clients.forEach(function (client) {
                if (client.readyState === WebSocket.OPEN) client.send(msg);
            });
        });
        return;
    }

    // stationId is set — kiosk relay tap. Admin clients must NEVER receive these.
    console.log('UID from kiosk station', stationId, '— NOT forwarded to admin:', uid);

    // Skip kiosks entirely while any assign modal is open
    if (isAnyAssignModeActive()) return;

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
        const relayReg     = url.searchParams.get('reg')     || null;
        console.log('Relay connected from', req.socket.remoteAddress, '| station:', relayStation || '-', '| reg:', relayReg || '-');

        ws.on('message', function (raw) {
            const msg = raw.toString();
            console.log('Relay →', msg);
            try {
                const data = JSON.parse(msg);
                if (data.uid) broadcastUID(data.uid, relayStation, relayReg);
            } catch (e) { /* ignore */ }
        });

        ws.on('close', function () { console.log('Relay disconnected'); });
        ws.on('error', function (err) { console.error('Relay error:', err.message); });

    } else {
        // ── Browser client ────────────────────────────────────────────────
        const clientType = url.searchParams.get('type') || 'kiosk';
        const isAdmin    = clientType === 'admin';

        if (isAdmin) {
            const regId = url.searchParams.get('reg') || 'default';
            ws._regId = regId;
            if (!adminClients.has(regId)) adminClients.set(regId, new Set());
            adminClients.get(regId).add(ws);
            let total = 0; adminClients.forEach(function (s) { total += s.size; });
            console.log('Admin browser connected | reg:', regId, '| total admins:', total);
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
                    setAssignMode(data.assignMode, ws._regId);
                }
            } catch (e) { /* ignore */ }
        });

        ws.on('close', function () {
            if (isAdmin) {
                const regId = ws._regId || 'default';
                const set   = adminClients.get(regId);
                if (set) {
                    set.delete(ws);
                    if (set.size === 0) {
                        adminClients.delete(regId);
                        // Last admin for this reg disconnected — reset its assign mode
                        if (assignModes.has(regId)) setAssignMode(false, regId);
                    }
                }
                let total = 0; adminClients.forEach(function (s) { total += s.size; });
                console.log('Admin browser disconnected | reg:', regId, '| total admins:', total);
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
            if (ws._regId) {
                const set = adminClients.get(ws._regId);
                if (set) set.delete(ws);
            } else {
                kioskClients.forEach(function (set) { set.delete(ws); });
            }
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
