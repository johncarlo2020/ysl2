<x-app-layout>
    <div class="modal-landing modal fade" id="scanCompleteModal" tabindex="-1"
        aria-labelledby="memberIdTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <p class="member-id-title" id="memberIdTitle">Your Member ID</p>
                    <p class="member-id-code">{{ auth()->user()->code }}</p>
                    <button class="member-id-done" type="button" data-bs-dismiss="modal">DONE</button>
                </div>
            </div>
        </div>
    </div>
    <div id="stationPage" class="station-page main main-bg safari-padding">
        <div class="mb-3 branding-container" onclick="modal()">
            @include('components.branding')
        </div>
        <h1 class="welcome-txt">WELCOME TO THE ​<br>
            YSL LOVENUDE BEAUTY HOTEL​</h1>
        <div id="mainContent" class="text-content text-center">
            <div class="content">
                <h2 class="station-born">{{ env('APP_TITLE') }}<br>
                </h2>
            </div>
            <div class="mb-3 station-img">
                <img src="{{ asset('images/new/landing.webp') }}" alt="" />
            </div>
            <div class="content">
                <p class="px-2 landing-tagline">
                    Indulge your late-night lip cravings. </p>

                <p class="px-2 landing-tagline">

                    Discover the new YSL LOVENUDE LIP STAIN,
                    a new-generation lip stain that fuses the freshness of a serum with the softness of a blurred
                    finish.
                </p>
            </div>
            <div class="mt-5 w-100 container">
                <a class="button-discover {{ auth()->user()->rfid_uid ? '' : 'disabled' }}"
                    @if (auth()->user()->rfid_uid) href="{{ route('dashboard') }}" @else aria-disabled="true" @endif>DISCOVER NOW</a>
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
