<x-app-layout>
    <div class="modal fade" id="scanCompleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="text-center content">
                        <div class="image-check">
                            <i class="fa-regular check" style="font-size: 25px; margin-bottom: 25px;"></i>
                        </div>
                        <div class="text-content">
                            <p class="station-name-modal">

                            </p>
                            <p class="message">Check-in Successful</p>
                        </div>
                        <div class="">
                            <a href="{{ route('dashboard') }}" class="button">
                                Next
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="stationPage" class="station-page main main-bg">
        <div class="branding-container">
            @include('components.branding')
        </div>
        <div id="mainContent" class="text-content text-center">
            <div class="content">
                <!-- <h1 class="station-born">UNLEASH YOUR INNER LIGHTS</h1> -->
                <h2 class="my-4 station-name">

                    {{ $station->name }}
                </h2>
            </div>
            <div class="mt-3 station-img">
                <img src="{{ asset('images/stations/' . $station->id . '.webp') }}" alt="" />
            </div>

              <p class="tag-line">{{$station->description }}</p>


            @if ($user == false)

            <div class="mt-2 scanner-button container">
                <div id="rfid-status" class="mb-2" style="min-height:24px;"></div>
                {{-- Hidden input captures keystrokes from RFID reader (keyboard wedge) --}}
                <input type="text" id="rfid-input" autocomplete="off" inputmode="none"
                    style="position:absolute;opacity:0;width:1px;height:1px;border:none;outline:none;"
                    aria-label="RFID input" />
                <div id="tap-indicator" class="mt-3">
                    <img src="{{ asset('images/new/key.webp') }}" alt="NFC Key" style="width: 60px;" />
                    <p class="mt-2 text-dark" style="font-size:0.75rem;">PLEASE CHECK-IN WITH YOUR LOVENUDE HOTEL KEY UPON COMPLETION</p>
                </div>
            </div>
            @else
            <div class="scanner-button">
                <p class="mb-2" style="color: black;">Checked In</p>
                <a class="button" href="{{ route('dashboard') }}"> Back</a>
            </div>
            @endif
        </div>
        <div id="scannerContainer" class="scanner-container d-none">
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

    <script>
        // Private channel listener — opens modal when the server confirms a check-in for this user
        (function () {
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            var pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
                cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
                channelAuthorization: {
                    endpoint: '/broadcasting/auth',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                }
            });

            pusher.connection.bind('error', function (err) {
                console.error('Pusher connection error:', err);
            });

            var channel = pusher.subscribe('private-user.{{ auth()->id() }}');

            channel.bind('pusher:subscription_error', function (err) {
                console.error('Channel subscription failed:', err);
            });

            channel.bind('checked.in', function (data) {
                console.log('check-in event received:', data);
                var stationName = data.station_name || '{{ $station->name }}';

                if (data.station_id == 4) {
                    $('.station-name-modal').html('GIFT HAS BEEN SUCCESSFULLY REDEEMED');
                    $('.message').addClass('d-none');
                } else {
                    $('.station-name-modal').html(stationName);
                }

                $('.check').removeClass('fa-circle-xmark text-danger').addClass('fa-circle-check text-success');
                $('#scanCompleteModal').modal('show');
            });
        })();
    </script>

    <script>
        @if (!$user)
        (function () {
            var rfidBuffer = '';
            var rfidTimer = null;
            var processing = false;

            var rfidInput = document.getElementById('rfid-input');
            var rfidStatus = document.getElementById('rfid-status');

            // Keep the hidden input focused so the RFID reader (keyboard wedge) types into it
            function keepFocus() {
                if (!processing) {
                    rfidInput.focus({ preventScroll: true });
                }
            }
            document.addEventListener('click', keepFocus);
            keepFocus();

            rfidInput.addEventListener('input', function () {
                rfidBuffer = rfidInput.value;
                clearTimeout(rfidTimer);
                // RFID readers send all chars very fast then Enter — process after short idle
                rfidTimer = setTimeout(function () {
                    if (rfidBuffer.trim().length > 0) {
                        processRfid(rfidBuffer.trim());
                        rfidBuffer = '';
                        rfidInput.value = '';
                    }
                }, 100);
            });

            rfidInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    clearTimeout(rfidTimer);
                    var uid = rfidInput.value.trim();
                    rfidInput.value = '';
                    rfidBuffer = '';
                    if (uid.length > 0) {
                        processRfid(uid);
                    }
                }
            });

            function processRfid(uid) {
                if (processing) return;
                processing = true;
                setStatus('info', 'Processing...');

                var csrfToken = $('meta[name="csrf-token"]').attr('content');

                $.ajax({
                    url: '{{ route('rfid.tap') }}',
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    data: {
                        rfid_uid: uid,
                        station_id: {{ $station->id }}
                    },
                    success: function (response) {
                        var stationName = '{{ $station->name }}';
                        $('.station-name-modal').html(stationName);

                        @if ($station->id == 4)
                        $('.station-name-modal').html('GIFT HAS BEEN SUCCESSFULLY REDEEMED');
                        $('.message').addClass('d-none');
                        @endif

                        $('.check').addClass('fa-circle-check text-success');
                        $('#scanCompleteModal').modal('show');
                    },
                    error: function (xhr) {
                        var msg = 'Card not recognised. Please try again.';
                        if (xhr.status === 404) msg = 'RFID card not assigned to any user.';
                        setStatus('danger', msg);
                        $('.check').addClass('fa-circle-xmark text-danger');
                        $('.message').html(msg);
                        $('#scanCompleteModal').modal('show');
                        processing = false;
                        keepFocus();
                    }
                });
            }

            function setStatus(type, msg) {
                var colors = { info: '#fff', danger: '#f44336', success: '#4caf50' };
                rfidStatus.style.color = colors[type] || '#fff';
                rfidStatus.textContent = msg;
            }
        })();
        @endif
    </script>
</x-app-layout>
