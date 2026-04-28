/**
 * hub.js — runs on the VPS
 *
 * Listens on 127.0.0.1:3000 (Apache proxies wss://host/nfc-ws → here).
 * Also listens on 127.0.0.1:3001 for HTTP POST /push from Laravel.
 *
 * Two types of WebSocket connections:
 *   ?token=<RFID_TOKEN>  → the relay (sends card UIDs)
 *   (no token)           → browser clients (receive card UIDs)
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

const wss      = new WebSocket.Server({ host: '127.0.0.1', port: PORT });
const browsers = new Set();

function broadcastUID(uid) {
    const msg = JSON.stringify({ uid });
    console.log('Broadcasting UID to', browsers.size, 'browser(s):', uid);
    browsers.forEach(function (client) {
        if (client.readyState === WebSocket.OPEN) {
            client.send(msg);
        }
    });
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
        console.log('Relay connected from', req.socket.remoteAddress);

        ws.on('message', function (raw) {
            const msg = raw.toString();
            console.log('Relay →', msg);
            try {
                const data = JSON.parse(msg);
                if (data.uid) broadcastUID(data.uid);
            } catch (e) { /* ignore */ }
        });

        ws.on('close', function () { console.log('Relay disconnected'); });
        ws.on('error', function (err) { console.error('Relay error:', err.message); });

    } else {
        // ── Browser client ────────────────────────────────────────────────
        browsers.add(ws);
        console.log('Browser connected (' + browsers.size + ' total)');

        ws.on('close', function () {
            browsers.delete(ws);
            console.log('Browser disconnected (' + browsers.size + ' remaining)');
        });

        ws.on('error', function (err) {
            console.error('Browser WS error:', err.message);
            browsers.delete(ws);
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

            broadcastUID(data.uid);
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
