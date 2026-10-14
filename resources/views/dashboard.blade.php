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
                           {{$station->id}}. {{ $station->name }}
                        </p>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const station4Link = document.getElementById('station-link-4');
            const station4Url = @json(route('station.show', ['station' => 4]));

            function prerequisitesComplete() {
                return [1, 2, 3].every(function (id) {
                    const tile = document.getElementById('station-' + id);
                    return tile && tile.classList.contains('active');
                });
            }

            function updateRewardLink() {
                if (station4Link) station4Link.href = prerequisitesComplete() ? station4Url : '#';
            }

            if (station4Link) {
                station4Link.addEventListener('click', function (event) {
                    if (!prerequisitesComplete()) {
                        event.preventDefault();
                        $('#scanCompleteModal').modal('show');
                    }
                });
            }

            document.addEventListener('user:checked-in', function (event) {
                const tile = document.getElementById('station-' + event.detail.station_id);
                if (tile) tile.classList.add('active');
                updateRewardLink();
            });
            document.addEventListener('user:state', function (event) {
                const completed = event.detail.completed_station_ids;
                document.querySelectorAll('[id^="station-"]').forEach(function (tile) {
                    const match = /^station-(\d+)$/.exec(tile.id);
                    if (match) tile.classList.toggle('active', completed.includes(Number(match[1])));
                });
                updateRewardLink();
            });
            updateRewardLink();
        });
    </script>
</x-app-layout>
