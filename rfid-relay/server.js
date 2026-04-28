const { NFC } = require('nfc-pcsc');
const https = require('https');
const http = require('http');

const LARAVEL_URL = process.env.LARAVEL_URL || 'https://nfctest.wowbynow.com.my/rfid/receive';
const RFID_TOKEN  = process.env.RFID_TOKEN  || 'ysl-rfid-secret-2026';

console.log('--- NFC Relay ---');
console.log('POSTing card UIDs to:', LARAVEL_URL);

const nfc = new NFC();

nfc.on('reader', function (reader) {
    console.log('Reader connected:', reader.name);

    reader.on('card', function (card) {
        const uid = card.uid.toUpperCase();
        console.log('Card tapped:', uid);
        postToLaravel(uid);
    });

    reader.on('error', function (err) { console.error('Reader error:', err); });
    reader.on('end',   function ()    { console.log('Reader removed:', reader.name); });
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


