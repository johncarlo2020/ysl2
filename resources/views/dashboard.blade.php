<x-app-layout>
    <div class="dashboard main main-bg safari-padding">
        <div class="branding-container">@include('components.branding')</div>
        <h1 class="station-born">CELEBRATE IN GOLD</h1>

        <div class="content">
            @foreach ($stations as $station)
            @if($station->id != 5)
            <a class="title-container" href="{{ route('station.show', ['station' => $station->id]) }}">
                <div class="tile">
                    <div class="img-container {{$station->status == true ? 'active':''}}">
                        <img src="{{ asset('images/S' . $station->id . '.jpg') }}" alt="" />
                        <div class="marker">
                            <p>CHECK-IN SUCCESSFUL</p>
                        </div>
                    </div>
                    <div class="text-container-dashboard">
                        <p class="station-name-dashboard">
                            {{$station->id}}

                            {{ $station->name }}
                        </p>
                    </div>
                </div>
            </a>
            @endif
            @endforeach
        </div>
    </div>
</x-app-layout>
