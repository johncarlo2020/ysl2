<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Station {{ $station->id }} — {{ $station->name }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Open Sans', sans-serif;
            background: #0a0a0a;
            color: #fff;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            user-select: none;
            overflow: hidden;
        }

        .station-label {
            font-size: 0.85rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #aaa;
            margin-bottom: 8px;
        }

        .station-name {
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-align: center;
            margin-bottom: 48px;
            color: #e5c97e;
        }

        .card-icon {
            font-size: 5rem;
            color: #e5c97e;
            margin-bottom: 24px;
            transition: transform 0.15s ease, color 0.15s ease;
        }

        .card-icon.pulse {
            animation: pulse 0.4s ease;
        }

        @keyframes pulse {
            0%   { transform: scale(1);    color: #e5c97e; }
            40%  { transform: scale(1.25); color: #fff; }
            100% { transform: scale(1);    color: #e5c97e; }
        }

        .instruction {
            font-size: 1.1rem;
            color: #ccc;
            text-align: center;
            margin-bottom: 32px;
        }

        /* Status badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 1rem;
            font-weight: 600;
            padding: 10px 28px;
            border-radius: 50px;
            min-width: 280px;
            justify-content: center;
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(8px);
        }

        .status-badge.show {
            opacity: 1;
            transform: translateY(0);
        }

        .status-badge.success {
            background: rgba(76, 175, 80, 0.15);
            border: 1px solid #4caf50;
            color: #4caf50;
        }

        .status-badge.error {
            background: rgba(244, 67, 54, 0.15);
            border: 1px solid #f44336;
            color: #f44336;
        }

        .status-badge.duplicate {
            background: rgba(255, 193, 7, 0.15);
            border: 1px solid #ffc107;
            color: #ffc107;
        }

        .status-badge.processing {
            background: rgba(229, 201, 126, 0.1);
            border: 1px solid #e5c97e;
            color: #e5c97e;
        }

        /* Hidden RFID keyboard-wedge capture input */
        #rfid-capture {
            position: absolute;
            opacity: 0;
            width: 1px;
            height: 1px;
            border: none;
            outline: none;
            pointer-events: none;
        }

        .footer-hint {
            position: fixed;
            bottom: 16px;
            font-size: 0.72rem;
            color: #444;
            letter-spacing: 0.05em;
        }

        /* Check-in success modal */
        .checkin-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.75);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .checkin-modal-overlay.active {
            display: flex;
        }

        .checkin-modal {
            background: #1a1a1a;
            border: 1px solid #e5c97e;
            border-radius: 16px;
            padding: 48px 56px;
            text-align: center;
            max-width: 420px;
            width: 90%;
            animation: modalIn 0.3s ease;
        }

        @keyframes modalIn {
            from { opacity: 0; transform: scale(0.88); }
            to   { opacity: 1; transform: scale(1); }
        }

        .checkin-modal .modal-icon {
            font-size: 4rem;
            color: #4caf50;
            margin-bottom: 20px;
        }

        .checkin-modal .modal-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #e5c97e;
            margin-bottom: 8px;
            letter-spacing: 0.05em;
        }

        .checkin-modal .modal-subtitle {
            font-size: 1rem;
            color: #ccc;
        }
    </style>
</head>
<body>

    <p class="station-label">Station {{ $station->id }}</p>
    <h1 class="station-name">{{ strtoupper($station->name) }}</h1>

    <i class="fa-solid fa-id-card card-icon" id="card-icon"></i>
    <p class="instruction">Tap your RFID card on the reader</p>

    <div class="status-badge" id="status-badge">
        <i class="fa-solid fa-circle-check" id="status-icon"></i>
        <span id="status-text">Ready</span>
    </div>

    {{-- Hidden input — RFID keyboard-wedge reader types here automatically --}}
    <input type="text" id="rfid-capture" autocomplete="off" aria-label="RFID reader input" />

    {{-- Check-in success modal --}}
    <div class="checkin-modal-overlay" id="checkin-modal-overlay">
        <div class="checkin-modal">
            <div class="modal-icon"><i class="fa-solid fa-circle-check"></i></div>
            <p class="modal-title" id="modal-title">
                @if ($station->id == 4)
                    GIFT HAS BEEN SUCCESSFULLY REDEEMED
                @else
                    {{ strtoupper($station->name) }}
                @endif
            </p>
            @if ($station->id != 4)
            <p class="modal-subtitle">Check-in Successful</p>
            @endif
        </div>
    </div>

    <p class="footer-hint">{{ $station->name }} &nbsp;|&nbsp; Keep this tab open at all times</p>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        (function () {
            var input       = document.getElementById('rfid-capture');
            var icon        = document.getElementById('card-icon');
            var badge       = document.getElementById('status-badge');
            var statusText  = document.getElementById('status-text');
            var statusIcon  = document.getElementById('status-icon');

            var buffer      = '';
            var timer       = null;
            var processing  = false;
            var resetTimer  = null;
            var modalTimer  = null;
            var modalOverlay = document.getElementById('checkin-modal-overlay');

            // Keep focus on hidden input at all times
            function keepFocus() { if (!processing) input.focus(); }
            document.addEventListener('click', keepFocus);
            document.addEventListener('keydown', keepFocus);
            keepFocus();

            // Buffer keystrokes — RFID reader fires all chars in <50 ms then sends Enter
            input.addEventListener('input', function () {
                buffer = input.value;
                clearTimeout(timer);
                timer = setTimeout(function () {
                    var uid = buffer.trim();
                    buffer = '';
                    input.value = '';
                    if (uid.length > 0) processRfid(uid);
                }, 150);
            });

            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    clearTimeout(timer);
                    var uid = input.value.trim();
                    input.value = '';
                    buffer = '';
                    if (uid.length > 0) processRfid(uid);
                }
            });

            function processRfid(uid) {
                if (processing) return;
                processing = true;
                clearTimeout(resetTimer);

                setStatus('processing', 'fa-spinner fa-spin', 'Processing...');
                icon.classList.add('pulse');
                setTimeout(function () { icon.classList.remove('pulse'); }, 400);

                $.ajax({
                    url: '{{ route('rfid.tap') }}',
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: {
                        rfid_uid: uid,
                        station_id: {{ $station->id }}
                    },
                    success: function (response) {
                        if (response.status === 'duplicate') {
                            setStatus('duplicate', 'fa-triangle-exclamation', 'Already checked in');
                        } else {
                            setStatus('success', 'fa-circle-check', 'Check-in successful!');
                            showCheckinModal();
                        }
                        autoReset(3000);
                    },
                    error: function (xhr) {
                        var msg = 'Card not recognised';
                        if (xhr.status === 404) msg = 'Card not assigned to any user';
                        if (xhr.status === 422) {
                            try {
                                var resp = JSON.parse(xhr.responseText);
                                if (resp.status === 'prerequisites_not_met') {
                                    msg = 'Complete stations 1, 2 & 3 first';
                                }
                            } catch (e) {}
                        }
                        setStatus('error', 'fa-circle-xmark', msg);
                        autoReset(4000);
                    }
                });
            }

            function setStatus(type, iconClass, text) {
                badge.className = 'status-badge show ' + type;
                statusIcon.className = 'fa-solid ' + iconClass;
                statusText.textContent = text;
            }

            function autoReset(delay) {
                resetTimer = setTimeout(function () {
                    badge.className = 'status-badge';
                    processing = false;
                    keepFocus();
                }, delay);
            }

            function showCheckinModal() {
                clearTimeout(modalTimer);
                modalOverlay.classList.add('active');
                modalTimer = setTimeout(function () {
                    modalOverlay.classList.remove('active');
                }, 4000);
            }

            // ── WebSocket — NFC relay broadcasts card UIDs to this page ──────────
            (function connectWS() {
                var wsProto = location.protocol === 'https:' ? 'wss:' : 'ws:';
                var ws = new WebSocket(wsProto + '//' + location.host + '/nfc-ws?type=kiosk&station={{ $station->id }}');

                ws.onopen = function () { console.log('NFC relay connected'); };

                ws.onmessage = function (event) {
                    try {
                        var data = JSON.parse(event.data);
                        if (data.uid) processRfid(data.uid);
                    } catch (e) { /* ignore */ }
                };

                ws.onclose = function () {
                    console.log('NFC relay disconnected — retrying in 3 s');
                    setTimeout(connectWS, 3000);
                };

                ws.onerror = function () { ws.close(); };
            })();
            // ─────────────────────────────────────────────────────────────────────
        })();
    </script>
</body>
</html>
