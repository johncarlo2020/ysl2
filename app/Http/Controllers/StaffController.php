<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['search' => ['nullable', 'string', 'max:255']]);
        $staff = User::whereHas('roles', fn ($query) => $query->where('name', 'staff'))->with('roles')
            ->when($data['search'] ?? null, fn ($query, $search) => $query->where('email', 'like', '%' . $search . '%'))
            ->orderBy('email')->get();
        return view('staff.index', ['staff' => $staff, 'stations' => Station::orderBy('id')->get(), 'staffData' => $staff->map(fn ($member) => ['id' => $member->id, 'email' => $member->email, 'staff_function' => $member->staff_function, 'station_id' => $member->station_id])->keyBy('id')]);
    }

    public function create()
    {
        return view('staff.form', ['staff' => new User(), 'stations' => Station::orderBy('id')->get()]);
    }

    public function edit(User $staff)
    {
        $this->staffOnly($staff);
        return view('staff.form', ['staff' => $staff, 'stations' => Station::orderBy('id')->get()]);
    }

    private function staffOnly(User $staff): void
    {
        abort_unless($staff->hasRole('staff') && !$staff->hasRole('admin'), 404);
    }

    private function validated(Request $request, ?User $staff = null): array
    {
        return $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($staff?->id)],
            'staff_function' => ['required', Rule::in(['register', 'station'])],
            'station_id' => [Rule::requiredIf($request->input('staff_function') === 'station'), 'nullable', 'integer', 'exists:stations,id'],
            'password' => [$staff ? 'nullable' : 'required', 'string', 'min:12', 'max:255', 'confirmed'],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        DB::transaction(function () use ($data) {
            $staff = User::create(['email' => $data['email'], 'code' => 'tauri-' . Str::uuid(), 'password' => Hash::make($data['password'])]);
            $staff->forceFill(['staff_function' => $data['staff_function'], 'station_id' => $data['staff_function'] === 'station' ? $data['station_id'] : null])->save();
            $staff->assignRole(Role::findOrCreate('staff', 'web'));
        });
        if ($request->expectsJson()) { return response()->json(['message' => 'Staff account created.']); }
        return redirect()->route('staff.index')->with('status', 'Staff account created.');
    }

    public function update(Request $request, User $staff)
    {
        $this->staffOnly($staff);
        $data = $this->validated($request, $staff);
        DB::transaction(function () use ($data, $staff) {
            $staff->forceFill(['email' => $data['email'], 'staff_function' => $data['staff_function'], 'station_id' => $data['staff_function'] === 'station' ? $data['station_id'] : null]);
            if (!empty($data['password'])) {
                $staff->password = Hash::make($data['password']);
            }
            $staff->save();
            $staff->tokens()->delete();
        });
        if ($request->expectsJson()) { return response()->json(['message' => 'Staff account updated.']); }
        return redirect()->route('staff.index')->with('status', 'Staff account updated. The staff member must sign in again.');
    }
}
