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
                <h2 class="station-born ">CELEBRATE THE SEASON <br> IN GOLD</h2>
            </div>
            <div class="station-img">
                <img src="{{ asset('images/new/MAIN.webp') }}" alt="" />
            </div>
            <div class="content">
                <p class="landing-tagline">Indulge in YSL Beauty's most coveted icons to reveal your inner radiance.</p>
            </div>
            <div class="container mt-2 w-50">
                <a class="button-discover" href="{{ route('dashboard') }}"> DISCOVER NOW</a>
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
