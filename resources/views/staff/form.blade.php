@extends('layouts.admin')
@section('content')
<div class="mt-4" style="max-width:720px">
    <h4>{{ $staff->exists ? 'Edit staff user' : 'Add staff user' }}</h4>
    <p>Registration staff manage RFID links. Station staff check in at their assigned station.</p>
    <div class="card"><div class="card-body">
    <form method="POST" action="{{ $staff->exists ? route('staff.update', $staff) : route('staff.store') }}">
        @csrf @if($staff->exists) @method('PUT') @endif
        @if($errors->any())<div class="alert alert-danger text-white" role="alert">Please correct the fields below.</div>@endif
        <div class="mb-3"><label for="email">Email</label><input id="email" name="email" type="email" class="form-control" value="{{ old('email', $staff->email) }}" required maxlength="255" autocomplete="off">@error('email')<p class="text-danger text-sm">{{ $message }}</p>@enderror</div>
        <div class="mb-3"><label for="staff_function">Function</label><select id="staff_function" name="staff_function" class="form-control" required><option value="register" @selected(old('staff_function', $staff->staff_function) === 'register')>Registration</option><option value="station" @selected(old('staff_function', $staff->staff_function) === 'station')>Station</option></select>@error('staff_function')<p class="text-danger text-sm">{{ $message }}</p>@enderror</div>
        <div class="mb-3" id="station-field"><label for="station_id">Assigned station</label><select id="station_id" name="station_id" class="form-control"><option value="">Select a station</option>@foreach($stations as $station)<option value="{{ $station->id }}" @selected((string) old('station_id', $staff->station_id) === (string) $station->id)>{{ $station->id }} · {{ $station->name }}</option>@endforeach</select>@error('station_id')<p class="text-danger text-sm">{{ $message }}</p>@enderror</div>
        <div class="mb-3"><label for="password">{{ $staff->exists ? 'New password (optional)' : 'Password' }}</label><input id="password" name="password" type="password" class="form-control" minlength="12" maxlength="255" autocomplete="new-password" @required(!$staff->exists)><p class="text-sm mt-2">At least 12 characters. {{ $staff->exists ? 'Leave blank to keep the current password.' : '' }}</p>@error('password')<p class="text-danger text-sm">{{ $message }}</p>@enderror</div>
        <div class="mb-4"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password" @required(!$staff->exists)></div>
        @if($staff->exists)<p class="text-sm">Saving changes signs this staff member out of the Tauri app.</p>@endif
        <div class="d-flex gap-2"><button class="btn btn-primary">{{ $staff->exists ? 'Save changes' : 'Create account' }}</button><a class="btn btn-secondary" href="{{ route('staff.index') }}">Cancel</a></div>
    </form>
    </div></div>
</div>
<script>
const staffFunction = document.getElementById('staff_function');
const station = document.getElementById('station_id');
function updateStation() { const needed = staffFunction.value === 'station'; document.getElementById('station-field').hidden = !needed; station.disabled = !needed; station.required = needed; }
staffFunction.addEventListener('change', updateStation); updateStation();
</script>
@endsection
