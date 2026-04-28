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
            <div class="p-3 pb-0 card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">RFID Card Assignment</h6>
                <small class="text-muted">Tap an RFID card on the reader or type the UID manually to assign it to a user.</small>
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
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Assign RFID Modal -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
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
    // ── Pusher — receive card UIDs from NFC relay via Laravel ───────────────
    var pusher  = new Pusher('{{ env('PUSHER_APP_KEY') }}', { cluster: '{{ env('PUSHER_APP_CLUSTER') }}' });
    var channel = pusher.subscribe('rfid');

    channel.bind('card.tapped', function (data) {
        var uid = data.uid;
        console.log('Card tapped:', uid);
        if (modalOpen && currentUserId) {
            $('#rfid-uid-input').val(uid);
            saveRfid(uid);
        } else {
            showToast('Card tapped: ' + uid + ' — open a user row to assign.');
        }
    });

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

    document.addEventListener('keydown', function (e) {
        if (!modalOpen) return;

        // Printable characters → accumulate in buffer
        if (e.key.length === 1) {
            rfidBuffer += e.key;
            // Prevent the keystroke from landing in DataTables search or elsewhere
            e.stopPropagation();

            // Also fill the visible input so staff can see what's being scanned
            $('#rfid-uid-input').val(rfidBuffer);

            clearTimeout(rfidTimer);
            rfidTimer = setTimeout(function () {
                // Auto-save if reader finished typing but didn't send Enter
                if (rfidBuffer.trim().length > 0) {
                    saveRfid(rfidBuffer.trim());
                    rfidBuffer = '';
                }
            }, 300);
            return;
        }

        if (e.key === 'Enter') {
            e.preventDefault();
            e.stopPropagation();
            clearTimeout(rfidTimer);
            var uid = rfidBuffer.trim() || $('#rfid-uid-input').val().trim();
            rfidBuffer = '';
            if (uid.length > 0) {
                saveRfid(uid);
            }
        }

        if (e.key === 'Backspace') {
            rfidBuffer = rfidBuffer.slice(0, -1);
            $('#rfid-uid-input').val(rfidBuffer);
        }
    }, true); // capture phase — fires before any other handler
    // ────────────────────────────────────────────────────────────────────────

    // Open modal
    $(document).on('click', '.assign-btn', function () {
        currentUserId = $(this).data('user-id');
        $('#modal-user-code').text($(this).data('user-code'));
        $('#rfid-uid-input').val('');
        $('#assign-alert').addClass('d-none').html('');
        rfidBuffer = '';
    });

    $('#assignModal').on('shown.bs.modal', function () {
        modalOpen = true;
        rfidBuffer = '';
        $('#rfid-uid-input').focus();
    });

    $('#assignModal').on('hidden.bs.modal', function () {
        modalOpen = false;
        rfidBuffer = '';
        clearTimeout(rfidTimer);
    });

    // Manual save button
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
