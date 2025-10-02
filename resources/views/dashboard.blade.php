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
                        <div class="image-check d-flex justify-content-center">
                            <div class="border rounded-circle d-flex justify-content-center align-items-center"
                                style="width: 50px; height: 50px; margin-bottom:20px;">
                                <i class="fa-solid fa-exclamation d-block" style="font-size: 25px;
                                                                    "></i>
                            </div>
                        </div>

                        <div class="text-content">
                            <p class="px-5 station-name-modal">
                            WHAT MAKES YOU LIBRE ?
                            </p>
                            <p class="px-5 message">Kindly complete
                                Station 1 - 3 to proceed to the Gift Redemption Station</p>
                        </div>
                        <div class="">
                            <button type="button" onclick="test()" class="button" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="dashboard main main-bg safari-padding">
        <div class="branding-container">@include('components.branding')</div>
        <h1 class="station-born">YOUR FOREVER <br> SCENT OF FREEDOM</h1>
        <br>
        <h5 class="text-center landing-description">Click on any of the stations below <br> to begin your adventure.</h2>

        <div class="content">
            @foreach ($stations as $station)
            <a class="title-container" id="station-link-{{ $station->id }}" href="{{ route('station.show', ['station' => $station->id]) }}">
                <div class="tile {{ $station->id %2 == 0? '':'reverse' }}">
                    <p class="station-number">{{$station->id}}</p>
                    <div id="station-{{ $station->id }}" class="img-container {{$station->status == true ? 'active':''}}">
                        <img src="{{ asset('images/libre' . $station->id . '.webp') }}" alt="" />
                        <div class="marker">
                            <p>CHECK-IN SUCCESSFUL</p>
                        </div>
                    </div>
                    <div class="text-container-dashboard">
                        <p class="station-name-dashboard">
                            {{ $station->name }}
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

        function test() {
            $('#scanCompleteModal').modal('hide');
        }
    </script>
</x-app-layout>
