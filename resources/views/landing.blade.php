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
                <p class="landing-tagline px-2">Dare to celebrate the holiday season unapologetically. From bold fragrances to starriest makeup icons.<br> <br>

                Adorn yourself. Express your edge.</p>
            </div>
            <div class="container mt-5" style="width: 65%;">
                <a class="button-discover" href="{{ route('dashboard') }}"> CLICK TO START JOURNEY</a>
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
