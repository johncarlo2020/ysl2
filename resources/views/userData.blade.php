@extends('layouts.admin')

@section('content')
    <style>
        .user-stations-panel { max-width: 800px; margin: 0 auto; }
        .user-station-row { display: flex; align-items: center; justify-content: space-between; gap: 24px; padding: 20px; }
        .user-station-row:nth-child(even) { background: #f6f7f9; }
        .user-station-row label { margin: 0; cursor: pointer; }
        .user-station-row .big-checkbox { width: 22px; height: 22px; flex-shrink: 0; cursor: pointer; accent-color: #5e72e4; }
        .user-station-row .big-checkbox:disabled { cursor: default; }
    </style>
    <div class="py-4 user-stations-panel">
        <a href="{{ route('users') }}" class="d-inline-block mb-3 text-white">
            <i class="fa-solid fa-arrow-left me-2" aria-hidden="true"></i>Back to users
        </a>
        <div class="card mb-4">
            <div class="card-body">
                <p class="text-sm text-uppercase text-secondary mb-2">User ID</p>
                <h4 class="mb-0 text-break">{{ $user->code ?: $user->id }}</h4>
            </div>
        </div>
        <div class="card overflow-hidden">
            <div class="card-header pb-3">
                <h5 class="mb-0">Station checks</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-unstyled mb-0">
                    @foreach ($user['stations'] as $station)
                        <li class="user-station-row">
                            <label for="station_checkbox_{{ $station['id'] }}" class="text-sm text-dark font-weight-bold">
                                #{{ $station['id'] }} {{ $station['name'] }}
                            </label>
                            <input type="checkbox" data-id="{{ $station['id'] }}"
                                id="station_checkbox_{{ $station['id'] }}" class="big-checkbox"
                                {{ $station['value'] ? 'checked' : '' }}
                                @disabled(!auth()->user()->hasPermissionTo('full'))>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    <script>
        document.querySelectorAll('.big-checkbox:not(:disabled)').forEach(function (checkbox) {
            checkbox.addEventListener('change', async function () {
                const newState = checkbox.checked;
                checkbox.disabled = true;
                try {
                    const response = await fetch(@json(route('check')), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            user_id: @json($user->id),
                            station_id: checkbox.dataset.id,
                            checked: newState,
                        }),
                    });
                    if (!response.ok) throw new Error('Unable to save station completion. Please refresh and try again.');
                    window.location.reload();
                } catch (error) {
                    checkbox.checked = !newState;
                    checkbox.disabled = false;
                    alert(error.message);
                }
            });
        });
    </script>
@endsection
