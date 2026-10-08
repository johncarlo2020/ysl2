<x-app-layout>
    <div class="modal fade station-incomplete-modal" id="scanCompleteModal" tabindex="-1"
        role="dialog" aria-labelledby="station-incomplete-title" aria-describedby="station-incomplete-message">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <svg class="station-incomplete-icon" viewBox="0 0 40 40" aria-hidden="true">
                        <circle cx="20" cy="20" r="18" fill="none" stroke="currentColor" stroke-width="3" />
                        <path d="M20 12v9" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                        <circle cx="20" cy="28" r="1.7" fill="currentColor" />
                    </svg>
                    <h2 id="station-incomplete-title" class="station-incomplete-title">THE NEW NUDE OBSESSION</h2>
                    <p id="station-incomplete-message" class="station-incomplete-message">
                        Kindly complete Station 1 - 3 to proceed to<br>REDEEM YOUR GIFT
                    </p>
                    <button type="button" class="button station-incomplete-close" data-dismiss="modal">CLOSE</button>
                </div>
            </div>
        </div>
    </div>
    <div class="dashboard main main-bg safari-padding">
        <div class="branding-container">@include('components.branding')</div>
        <h1 class="station-born">THE NEW NUDE OBSESSION</h1>

        <div class="content">
            @foreach ($stations as $station)
            <a class="title-container" id="station-link-{{ $station->id }}" href="{{ route('station.show', ['station' => $station->id]) }}">
                <div class="tile {{ $station->id %2 == 0? '':'reverse' }}">
                    <div id="station-{{ $station->id }}" class="img-container {{$station->status == true ? 'active':''}}">
                        <img src="{{ asset('images/stations/' . $station->id . '.webp') }}" alt="" />
                        <div class="marker">
                            <p>CHECK-IN SUCCESSFUL</p>
                        </div>
                    </div>
                    <div class="text-container-dashboard">
                        <p class="station-name-dashboard">
                           {{$station->id}} {{ $station->name }}
                        </p>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const station1 = document.getElementById('station-1');
            const station2 = document.getElementById('station-2');
            const station3 = document.getElementById('station-3');

            const station4Link = document.getElementById('station-link-4');

            // Function to check if both station 1 and 2 are active
            function checkStationsActive() {
                const isStation1Active = station1.classList.contains('active');
                const isStation2Active = station2.classList.contains('active');
                const isStation3Active = station3.classList.contains('active');


                if (isStation1Active && isStation2Active && isStation3Active) {
                    // Enable the link for station 3
                    station4Link.style.pointerEvents = 'auto';
                    station4Link.style.cursor = 'pointer';
                    station4Link.href = '{{ route('station.show', ['station' => 4]) }}';
                } else {
                    // Disable the link for station 3

                    station4Link.href = '#'; // Prevent navigation
                    station4Link.addEventListener('click', openModal);
                }
            }

            function openModal(event) {
                event.preventDefault();
                $('#scanCompleteModal').modal('show');
            }

            // Check on initial load
            checkStationsActive();
            // Optionally: Add event listeners if the status of stations can change dynamically
            // (For example, if they can be updated via AJAX, or the status changes after some user action)
            station1.addEventListener('classChange', checkStationsActive);
            station2.addEventListener('classChange', checkStationsActive);
            station3.addEventListener('classChange', checkStationsActive);

        });

    </script>
</x-app-layout>
