@extends('layouts.admin')

@section('content')
<div class="mt-4 row">
    {{-- Station Kiosk Links --}}
    <div class="mb-4 col-lg-12">
        <div class="card">
            <div class="p-3 pb-0 card-header">
                <h6 class="mb-0">Station Kiosk Pages</h6>
                <small class="text-muted">Open each link on the dedicated device at that station and leave it open. The RFID reader must be plugged into that device.</small>
            </div>
            <div class="card-body d-flex flex-wrap gap-3 pt-3">
                @foreach (\App\Models\Station::orderBy('id')->get() as $station)
                <a href="{{ route('kiosk', $station->id) }}" target="_blank"
                    class="btn btn-outline-dark d-flex align-items-center gap-2">
                    <i class="fa-solid fa-id-card"></i>
                    Station {{ $station->id }}: {{ $station->name }}
                    <i class="fa-solid fa-arrow-up-right-from-square ms-1" style="font-size:0.7rem;"></i>
                </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- User RFID Assignment Table --}}
    <div class="mb-4 col-lg-12 mb-lg-0">
        <div class="card">
            <div class="p-3 pb-0 card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h6 class="mb-0">RFID Card Assignment</h6>
                    <small class="text-muted">Tap an RFID card on the reader or type the UID manually to assign it to a user.</small>
                </div>
                <button class="btn btn-sm btn-outline-secondary" id="check-card-btn" data-bs-toggle="modal" data-bs-target="#checkCardModal">
                    <i class="fa-solid fa-magnifying-glass me-1"></i> Check Card
                </button>
            </div>
            <div class="table-responsive">
                <table id="rfid-table" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>RFID UID</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->code }}</td>
                            <td>
                                <span class="rfid-display-{{ $user->id }}">
                                    @if ($user->rfid_uid)
                                        <span class="badge bg-success">{{ $user->rfid_uid }}</span>
                                    @else
                                        <span class="text-muted fst-italic">Not assigned</span>
                                    @endif
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary assign-btn"
                                    data-user-id="{{ $user->id }}"
                                    data-user-code="{{ $user->code }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#assignModal">
                                    <i class="fa-solid fa-id-card me-1"></i>
                                    {{ $user->rfid_uid ? 'Re-assign' : 'Assign' }}
                                </button>
                                @if ($user->rfid_uid)
                                <button class="btn btn-sm btn-danger unlink-btn ms-1"
                                    data-user-id="{{ $user->id }}"
                                    data-user-code="{{ $user->code }}">
                                    <i class="fa-solid fa-link-slash me-1"></i>Unlink
                                </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Check Card Modal -->
<div class="modal fade" id="checkCardModal" tabindex="-1" aria-labelledby="checkCardModalLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="checkCardModalLabel">Check RFID Card</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-sm text-muted mb-3">Tap the RFID card on the reader connected to this PC to check if it is already linked to a user.</p>
                <div class="form-group mb-3">
                    <label class="form-control-label">RFID UID</label>
                    <input type="text" id="check-uid-input" class="form-control" placeholder="Tap card or type UID..." autocomplete="off" inputmode="none" />
                </div>
                <button type="button" class="btn btn-secondary btn-sm mb-2" id="check-uid-btn">
                    <i class="fa-solid fa-magnifying-glass me-1"></i> Check
                </button>
                <div id="check-card-result" class="mt-2 d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Assign RFID Modal -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignModalLabel">Assign RFID Card</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-sm mb-1">Assigning card to user: <strong id="modal-user-code"></strong></p>
                <p class="text-xs text-muted mb-3">
                    <i class="fa-solid fa-wifi me-1 text-success"></i>
                    Tap the RFID card on the reader — the UID will fill in automatically via Pusher.
                    Or type it manually below.
                </p>
                <div class="form-group">
                    <label class="form-control-label">RFID UID</label>
                    <input type="text" id="rfid-uid-input" class="form-control" placeholder="Tap card or type UID here..." autocomplete="off" />
                </div>
                <div id="assign-alert" class="mt-2 d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="save-rfid-btn" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.dataTables.css">
<script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.datatables.net/2.0.7/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.dataTables.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.print.min.js"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
    // ── WebSocket — NFC relay broadcasts card UIDs to this page ─────────────
    var adminWs = null;
    (function connectWS() {
        var wsProto = location.protocol === 'https:' ? 'wss:' : 'ws:';
        var ws = new WebSocket(wsProto + '//' + location.host + '/nfc-ws?type=admin');
        adminWs = ws;

        ws.onopen = function () {
            console.log('NFC relay connected');
            // If the assign modal was already open when the WS reconnected
            // (e.g. hub restarted), re-send assignMode so kiosks stay blocked.
            if (modalOpen) {
                ws.send(JSON.stringify({ assignMode: true }));
            }
        };

        ws.onmessage = function (event) {
            try {
                var data = JSON.parse(event.data);
                var uid  = data.uid;
                if (!uid) return;
                // If the check card modal is open, route the tap there and do nothing else
                if ($('#checkCardModal').hasClass('show') || $('#checkCardModal').is(':visible')) {
                    checkCard(uid);
                    return;
                }
                var modalIsOpen = $('#assignModal').hasClass('show') || $('#assignModal').is(':visible');
                console.log('Card tapped:', uid, '| modalIsOpen:', modalIsOpen, '| currentUserId:', currentUserId);

                if (modalIsOpen && currentUserId) {
                    $('#rfid-uid-input').val(uid);
                    saveRfid(uid);
                } else if (modalOpen) {
                    // Modal is open but user row not yet selected — fill the input
                    $('#rfid-uid-input').val(uid);
                    rfidBuffer = uid;
                    showToast('Card tapped: ' + uid + ' — click Save to assign.');
                } else {
                    showToast('Card tapped: ' + uid + ' — open a user row to assign.');
                }
            } catch (e) {
                console.log(e) /* ignore */ }
        };

        ws.onclose = function () {
            adminWs = null;
            console.log('NFC relay disconnected — retrying in 3 s');
            setTimeout(connectWS, 3000);
        };

        ws.onerror = function () { ws.close(); };
    })();

    function showToast(msg) {
        var $t = $('<div class="alert alert-warning alert-dismissible fade show position-fixed" style="bottom:20px;right:20px;z-index:9999">' +
            msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
        $('body').append($t);
        setTimeout(function () { $t.alert('close'); }, 4000);
    }
    // ─────────────────────────────────────────────────────────────────────────

    var currentUserId = null;
    var table = $('#rfid-table').DataTable({
        responsive: true,
        dom: "<'row'<'col-sm-12 col-md-3'l><'col-sm-6 col-md-6 align-items-end'B><'col-sm-12 col-md-3'f>>" +
            "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        buttons: ['copy', 'csv', 'excel'],
        order: [[0, 'desc']]
    });

    // ── RFID keyboard-wedge capture ──────────────────────────────────────────
    // Intercept ALL keystrokes at document level while the modal is open.
    // RFID readers fire characters very fast then send Enter — we buffer them.
    var rfidBuffer = '';
    var rfidTimer  = null;
    var modalOpen  = false;
    var checkModalOpen = false;

    document.addEventListener('keydown', function (e) {
        var isCheckOpen  = $('#checkCardModal').hasClass('show') || $('#checkCardModal').is(':visible');
        var isAssignOpen = $('#assignModal').hasClass('show') || $('#assignModal').is(':visible');
        if (!isAssignOpen && !isCheckOpen) return;

        // Printable characters → accumulate in buffer
        if (e.key.length === 1) {
            rfidBuffer += e.key;
            e.stopPropagation();

            if (isCheckOpen) {
                $('#check-uid-input').val(rfidBuffer);
            } else {
                $('#rfid-uid-input').val(rfidBuffer);
            }

            clearTimeout(rfidTimer);
            rfidTimer = setTimeout(function () {
                if (rfidBuffer.trim().length > 0) {
                    if ($('#checkCardModal').hasClass('show') || $('#checkCardModal').is(':visible')) {
                        checkCard(rfidBuffer.trim());
                    } else {
                        saveRfid(rfidBuffer.trim());
                    }
                    rfidBuffer = '';
                }
            }, 300);
            return;
        }

        if (e.key === 'Enter') {
            e.preventDefault();
            e.stopPropagation();
            clearTimeout(rfidTimer);
            if (isCheckOpen) {
                var uid = rfidBuffer.trim() || $('#check-uid-input').val().trim();
                rfidBuffer = '';
                if (uid.length > 0) checkCard(uid);
            } else {
                var uid = rfidBuffer.trim() || $('#rfid-uid-input').val().trim();
                rfidBuffer = '';
                if (uid.length > 0) saveRfid(uid);
            }
        }

        if (e.key === 'Backspace') {
            rfidBuffer = rfidBuffer.slice(0, -1);
            if (isCheckOpen) {
                $('#check-uid-input').val(rfidBuffer);
            } else {
                $('#rfid-uid-input').val(rfidBuffer);
            }
        }
    }, true);
    // ────────────────────────────────────────────────────────────────────────

    // Open modal
    $(document).on('click', '.assign-btn', function () {
        currentUserId = $(this).data('user-id');
        $('#modal-user-code').text($(this).data('user-code'));
        $('#rfid-uid-input').val('');
        $('#assign-alert').addClass('d-none').html('');
        rfidBuffer = '';
    });

    // Unlink RFID
    $(document).on('click', '.unlink-btn', function () {
        var userId   = $(this).data('user-id');
        var userCode = $(this).data('user-code');
        if (!confirm('Remove RFID card from user ' + userCode + '?')) return;
        var $btn = $(this);
        $.ajax({
            url: '{{ route('rfid.unlink') }}',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { user_id: userId },
            success: function () {
                $('.rfid-display-' + userId).html('<span class="text-muted fst-italic">Not assigned</span>');
                $('[data-user-id="' + userId + '"].assign-btn').html('<i class="fa-solid fa-id-card me-1"></i> Assign');
                $btn.remove();
                showToast('RFID unlinked successfully.');
            },
            error: function () {
                showToast('Failed to unlink RFID.');
            }
        });
    });

    $('#assignModal').on('show.bs.modal', function (event) {
        // Also capture from the triggering button (belt-and-suspenders)
        var btn = event.relatedTarget;
        if (btn) {
            currentUserId = $(btn).data('user-id');
            $('#modal-user-code').text($(btn).data('user-code'));
        }
        modalOpen = true;
        // Tell the hub to stop forwarding card taps to kiosk pages
        if (adminWs && adminWs.readyState === WebSocket.OPEN) {
            adminWs.send(JSON.stringify({ assignMode: true }));
        }
    });

    $('#assignModal').on('shown.bs.modal', function () {
        modalOpen = true;
        rfidBuffer = '';
        $('#rfid-uid-input').focus();
    });

    $('#assignModal').on('hide.bs.modal', function () {
        // Move focus out before Bootstrap sets aria-hidden="true"
        if (this.contains(document.activeElement)) {
            document.activeElement.blur();
        }
    });

    $('#assignModal').on('hidden.bs.modal', function () {
        modalOpen = false;
        rfidBuffer = '';
        clearTimeout(rfidTimer);
        // Tell the hub to resume forwarding card taps to kiosk pages
        if (adminWs && adminWs.readyState === WebSocket.OPEN) {
            adminWs.send(JSON.stringify({ assignMode: false }));
        }
    });

    // ── Check Card modal ────────────────────────────────────────────────────
    $('#checkCardModal').on('shown.bs.modal', function () {
        checkModalOpen = true;
        rfidBuffer = '';
        $('#check-uid-input').val('');
        $('#check-card-result').addClass('d-none').html('');
        $('#check-uid-input').focus();
    });

    $('#checkCardModal').on('hide.bs.modal', function () {
        if (this.contains(document.activeElement)) document.activeElement.blur();
    });

    $('#checkCardModal').on('hidden.bs.modal', function () {
        checkModalOpen = false;
        rfidBuffer = '';
        clearTimeout(rfidTimer);
    });

    $('#check-uid-btn').on('click', function () {
        var uid = rfidBuffer.trim() || $('#check-uid-input').val().trim();
        rfidBuffer = '';
        if (!uid) return;
        checkCard(uid);
    });

    function checkCard(uid) {
        $('#check-uid-input').val(uid);
        $('#check-card-result').removeClass('d-none alert-success alert-danger alert-warning')
            .addClass('alert').html('Checking...');
        $.ajax({
            url: '{{ route('rfid.check') }}',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { rfid_uid: uid },
            success: function (res) {
                if (res.linked) {
                    $('#check-card-result')
                        .removeClass('alert-warning')
                        .addClass('alert-danger')
                        .html(
                            '<i class="fa-solid fa-circle-xmark me-1"></i> This card is already linked to user <strong>' + res.user_code + '</strong>.' +
                            '<br><button class="btn btn-sm btn-danger mt-2" id="check-unlink-btn" data-user-id="' + res.user_id + '" data-user-code="' + res.user_code + '">' +
                            '<i class="fa-solid fa-link-slash me-1"></i> Unlink from ' + res.user_code +
                            '</button>'
                        );
                } else {
                    $('#check-card-result')
                        .removeClass('alert-danger')
                        .addClass('alert-success')
                        .html('<i class="fa-solid fa-circle-check me-1"></i> This card is <strong>not linked</strong> to any user.');
                }
                rfidBuffer = '';
                $('#check-uid-input').val('').focus();
            },
            error: function () {
                $('#check-card-result')
                    .addClass('alert-warning')
                    .html('Error checking card. Please try again.');
            }
        });
    }
    // Unlink from Check Card modal
    $(document).on('click', '#check-unlink-btn', function () {
        var userId   = $(this).data('user-id');
        var userCode = $(this).data('user-code');
        if (!confirm('Remove RFID card from user ' + userCode + '?')) return;
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Unlinking...');
        $.ajax({
            url: '{{ route('rfid.unlink') }}',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { user_id: userId },
            success: function () {
                $('#check-card-result')
                    .removeClass('alert-danger')
                    .addClass('alert-success')
                    .html('<i class="fa-solid fa-circle-check me-1"></i> Card has been <strong>unlinked</strong> from user <strong>' + userCode + '</strong>.');
                // Update the main table row if visible
                $('.rfid-display-' + userId).html('<span class="text-muted fst-italic">Not assigned</span>');
                $('[data-user-id="' + userId + '"].assign-btn').html('<i class="fa-solid fa-id-card me-1"></i> Assign');
                $('[data-user-id="' + userId + '"].unlink-btn').remove();
            },
            error: function () {
                $btn.prop('disabled', false).html('<i class="fa-solid fa-link-slash me-1"></i> Unlink from ' + userCode);
                $('#check-card-result').after('<div class="alert alert-warning mt-1">Failed to unlink. Please try again.</div>');
            }
        });
    });
    // ────────────────────────────────────────────────────────────────────────
    $('#save-rfid-btn').on('click', function () {
        var uid = rfidBuffer.trim() || $('#rfid-uid-input').val().trim();
        rfidBuffer = '';
        if (!uid) {
            showAlert('danger', 'Please tap the RFID card or enter the UID manually.');
            return;
        }
        saveRfid(uid);
    });

    function saveRfid(uid) {
        $('#rfid-uid-input').val(uid);
        showAlert('info', 'Saving...');

        $.ajax({
            url: '{{ route('rfid.assign') }}',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { user_id: currentUserId, rfid_uid: uid },
            success: function (response) {
                showAlert('success', response.message);
                var $display = $('.rfid-display-' + currentUserId);
                $display.html('<span class="badge bg-success">' + uid + '</span>');
                $('[data-user-id="' + currentUserId + '"]').html('<i class="fa-solid fa-id-card me-1"></i> Re-assign');
                setTimeout(function () { $('#assignModal').modal('hide'); }, 1200);
            },
            error: function (xhr) {
                var msg = 'Failed to assign RFID.';
                if (xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.rfid_uid) {
                    msg = xhr.responseJSON.errors.rfid_uid[0];
                }
                showAlert('danger', msg);
                rfidBuffer = '';
                $('#rfid-uid-input').val('').focus();
            }
        });
    }

    function showAlert(type, message) {
        var colorMap = { success: 'success', danger: 'danger', info: 'primary' };
        $('#assign-alert')
            .removeClass('d-none alert-success alert-danger alert-primary')
            .addClass('alert alert-' + (colorMap[type] || 'secondary'))
            .text(message);
    }
</script>
@endsection
