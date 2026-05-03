<x-app-layout>
    <div class="modal-landing modal fade " id="scanCompleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="text-center content">
                        <div class="text-content">
                            <p class="station-name-modal">
                                Your Member Id
                            </p>
                            <p class="message">{{auth()->user()->code}}</p>
                        </div>
                        <div class="">
                            <button class="button" type="button" data-bs-dismiss="modal">Done</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="stationPage" class="station-page main main-bg safari-padding">
        <div class="mb-3 branding-container" onclick="modal()">
            @include('components.branding')
        </div>
        <div id="mainContent" class="text-center text-content">
            <div class="content">
                <h2 class="station-born ">{{ env('APP_TITLE') }}<br>
                </h2>
            </div>
            <div class="station-img">
                <img src="{{ asset('images/new/landing.webp') }}" alt="" />
            </div>
            <div class="content">
                <p class="landing-tagline px-2">Discover YSL LOVENUDE Lip Blusher, a soft blurring lip colour with 7H blur finish and care like a balm for an undressed sensual pout</p>
            </div>
            <div class="container mt-5" style="width: 65%;">
                @if (auth()->user()->rfid_uid)
                    <a class="button-discover" href="{{ route('dashboard') }}">DISCOVER NOW</a>
                @else
                    <span class="button-discover" style="opacity:0.4;cursor:not-allowed;pointer-events:none;">DISCOVER NOW</span>
                    <p class="text-white mt-3" style="font-size:0.8rem;opacity:0.7;">Please visit the registration desk to get your RFID wristband assigned before proceeding.</p>
                @endif
            </div>
        </div>
    </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        function modal() {
            $('#scanCompleteModal').modal('show');

        }
    </script>
</x-app-layout>
