<x-app-layout>
    <style>
        .message {
            color: black;
        }

        .station-name-modal {
            color: black;
            font-size: 20px;
            font-weight: bolder;
            letter-spacing: 3px;
        }
    </style>
    <div class="modal fade " id="scanCompleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="text-center content">
                        <div class="image-check">
                            {{-- <i class="fa-regular fa-circle-question"></i> --}}
                            <i class="fa-regular fa-circle-question" style="font-size: 25px;
    margin-bottom: 25px;"></i>
                        </div>
                        <div class="text-content">
                            <p class="station-name-modal">
                                CELEBRATE IN GOLD
                            </p>
                            <p class="message">Kindly complete
                                Station 1 - Station 2 to proceed to the Gift Redemption Station</p>
                        </div>
                        <div class="">
                            <button type="button" onclick="test()" class="button" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="dashboard main main-bg">

        <div class="branding-container">@include('components.branding')</div>
        <h1 class="station-born ">UNLEASH YOUR INNER LIGHTS THIS RAMADAN</h1>

        <div class="content">
            @foreach ($stations as $station)
            <a id="station-link-{{ $station->id }}" class="title-container"
                href="{{ route('station.show', ['station' => $station->id]) }}">
                <div class="tile">
                    <div id="station-{{ $station->id }}"
                        class="img-container {{ $station->status == true ? 'active' : '' }}">
                        <img src="{{ asset('images/S' . $station->id . '-main.webp') }}" alt="" />
                        <div class="marker">
                            <p>CHECK-IN SUCCESSFUL</p>
                        </div>
                    </div>
                    <div class="text-container-dashboard">
                        <p class="station-name-dashboard">
                            {{ $station->id }}.

                            {{ $station->name }}
                        </p>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const station1 = document.getElementById('station-1');
            const station2 = document.getElementById('station-2');
            const station3Link = document.getElementById('station-link-3');

            // Function to check if both station 1 and 2 are active
            function checkStationsActive() {
                const isStation1Active = station1.classList.contains('active');
                const isStation2Active = station2.classList.contains('active');

                if (isStation1Active && isStation2Active) {
                    // Enable the link for station 3
                    station3Link.style.pointerEvents = 'auto';
                    station3Link.style.cursor = 'pointer';
                    station3Link.href = '{{ route('station.show', ['station' => 3]) }}';
                } else {
                    // Disable the link for station 3

                    station3Link.href = '#'; // Prevent navigation
                    station3Link.addEventListener('click', openModal);
                }
            }

            function openModal(event) {
                $('#scanCompleteModal').modal('show');
            }

            // Check on initial load
            checkStationsActive();



            // Optionally: Add event listeners if the status of stations can change dynamically
            // (For example, if they can be updated via AJAX, or the status changes after some user action)
            station1.addEventListener('classChange', checkStationsActive);
            station2.addEventListener('classChange', checkStationsActive);
        });

        function test() {
            $('#scanCompleteModal').modal('hide');
        }
    </script>

</x-app-layout>
