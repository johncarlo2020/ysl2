<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Station;
use App\Http\Controllers\StationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['sometimes', 'string', 'max:255'],
        ]);
        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password) || !($user->hasRole('admin') || $this->validStaff($user))) {
            throw ValidationException::withMessages(['email' => 'The provided staff or admin credentials are invalid.']);
        }

        $expiresAt = $user->hasRole('admin') ? now()->addDay() : now()->addDays(30);
        $token = $user->createToken($data['device_name'] ?? 'tauri-nfc', ['nfc:manage'], $expiresAt);

        return response()->json([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->toIso8601String(),
            'user' => $this->userData($user),
            'realtime' => $this->realtimeConfig($user),
        ]);
    }

    private function realtimeConfig(User $user): ?array
    {
        if (!($user->hasRole('admin') || ($this->validStaff($user) && $user->staff_function === 'register')) ||
            config('broadcasting.default') !== 'pusher') {
            return null;
        }
        $settings = config('broadcasting.connections.pusher');
        if (empty($settings['key']) || empty($settings['secret']) || empty($settings['app_id'])) {
            return null;
        }
        return ['key' => $settings['key'], 'cluster' => $settings['options']['cluster'] ?? 'mt1'];
    }

    public function authorizeBroadcast(Request $request)
    {
        $this->authorizeAdmin($request, 'register');
        $request->validate([
            'socket_id' => ['required', 'string', 'max:64', 'regex:/^\d+\.\d+$/'],
            'channel_name' => ['required', 'string', 'in:private-nfc-registrations'],
        ]);
        abort_unless($this->realtimeConfig($request->user()), 503, 'Live registration updates are not configured.');
        return \Illuminate\Support\Facades\Broadcast::auth($request);
    }

    private function validStaff(User $user): bool
    {
        return $user->hasRole('staff') && (
            ($user->staff_function === 'register' && $user->station_id === null) ||
            ($user->staff_function === 'station' && Station::whereKey($user->station_id)->exists())
        );
    }

    private function authorizeAdmin(Request $request, ?string $function = null): void
    {
        $user = $request->user();
        abort_unless($user->tokenCan('nfc:manage') && ($user->hasRole('admin') ||
            ($this->validStaff($user) && ($function === null || $user->staff_function === $function))), 403);
    }

    private function userData(User $user): array
    {
        $completed = $user->stationUser->pluck('station_id')
            ->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();
        $redeemed = in_array(\App\Services\RewardEligibility::FINAL_STATION_ID, $completed, true);

        return [
            'id' => $user->id,
            'email' => $user->email,
            'code' => $user->code,
            'mobile_number' => $user->code,
            'rfid_uid' => $user->rfid_uid,
            'role' => $user->hasRole('admin') ? 'admin' : ($user->hasRole('staff') ? 'staff' : 'client'),
            'staff_function' => $user->staff_function,
            'station_id' => $user->station_id,
            'completed_station_ids' => $completed,
            'all_stations_completed' => empty(array_diff([1, 2, 3, 4], $completed)),
            'redeemed' => $redeemed,
            'can_link_nfc' => !$redeemed,
        ];
    }

    public function me(Request $request)
    {
        $this->authorizeAdmin($request);
        return response()->json(['data' => $this->userData($request->user()), 'realtime' => $this->realtimeConfig($request->user())]);
    }

    private function filterAssignments($query, string $status): void
    {
        $redeemed = fn ($query) => $query->where('station_id', \App\Services\RewardEligibility::FINAL_STATION_ID);
        if ($status === 'redeemed') {
            $query->whereHas('stationUser', $redeemed);
        } elseif ($status === 'assigned') {
            $query->whereNotNull('rfid_uid')->where('rfid_uid', '!=', '')
                ->whereDoesntHave('stationUser', $redeemed);
        } elseif ($status === 'unassigned') {
            $query->where(fn ($query) => $query->whereNull('rfid_uid')->orWhere('rfid_uid', ''))
                ->whereDoesntHave('stationUser', $redeemed);
        }
    }

    public function users(Request $request)
    {
        $this->authorizeAdmin($request, 'register');
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'assignment_status' => ['sometimes', 'in:all,assigned,unassigned,redeemed'],
            'without_nfc' => ['sometimes', 'boolean'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);
        $attendees = User::query()->whereDoesntHave('roles', fn ($query) => $query->whereIn('name', ['admin', 'staff']));
        // Global summary counts stay independent of search, status, and page.
        $counts = ['all' => (clone $attendees)->count()];
        foreach (['assigned', 'unassigned', 'redeemed'] as $status) {
            $countQuery = clone $attendees;
            $this->filterAssignments($countQuery, $status);
            $counts[$status] = $countQuery->count();
        }
        $query = (clone $attendees)->with(['roles', 'stationUser:id,user_id,station_id']);
        $this->filterAssignments($query, $data['assignment_status'] ?? 'all');
        // Keep the legacy flag's meaning, including released redeemed cards.
        if ($request->boolean('without_nfc')) {
            $query->where(fn ($query) => $query->whereNull('rfid_uid')->orWhere('rfid_uid', ''));
        }
        $search = trim($data['search'] ?? '');
        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('code', 'like', '%' . $search . '%')->orWhere('rfid_uid', $search);
                // Avoid MySQL coercing a nonnumeric card UID into a numeric ID.
                if (ctype_digit($search)) {
                    $query->orWhere('id', $search);
                }
            });
        }
        $users = $query->orderByDesc('id')->paginate($data['per_page'] ?? 25)->withQueryString();
        $users->through(fn ($user) => $this->userData($user));
        return response()->json(array_merge($users->toArray(), ['counts' => $counts]));
    }

    public function byRfid(Request $request)
    {
        $this->authorizeAdmin($request);
        $data = $request->validate([
            'rfid_uid' => ['required', 'string', 'max:64'],
        ]);
        $user = User::where('rfid_uid', trim($data['rfid_uid']))
            ->whereDoesntHave('roles', fn ($query) => $query->whereIn('name', ['admin', 'staff']))
            ->first();

        abort_unless($user, 404, 'RFID card not recognised');

        return response()->json(['data' => $this->userData($user)]);
    }

    public function show(Request $request, User $user)
    {
        $this->authorizeAdmin($request, 'register');
        abort_if($user->hasAnyRole(['admin', 'staff']), 404);
        return response()->json(['data' => $this->userData($user)]);
    }

    public function assign(Request $request, User $user)
    {
        $this->authorizeAdmin($request, 'register');
        abort_if($user->hasAnyRole(['admin', 'staff']), 404);
        app(\App\Services\RfidAssignment::class)->assign($user, $request->input('rfid_uid'));
        return response()->json(['message' => 'NFC assigned successfully.', 'data' => $this->userData($user)]);
    }

    public function unassign(Request $request, User $user)
    {
        $this->authorizeAdmin($request, 'register');
        abort_if($user->hasAnyRole(['admin', 'staff']), 404);
        app(\App\Services\RfidAssignment::class)->unassign($user);
        return response()->json(['message' => 'NFC unassigned successfully.', 'data' => $this->userData($user)]);
    }

    public function logout(Request $request)
    {
        $this->authorizeAdmin($request);
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function stations(Request $request)
    {
        $this->authorizeAdmin($request, 'station');
        return response()->json(['data' => Station::query()
            ->when(!$request->user()->hasRole('admin'), fn ($query) => $query->whereKey($request->user()->station_id))
            ->orderBy('id')->get(['id', 'name', 'description'])]);
    }

    public function checkIn(Request $request)
    {
        $this->authorizeAdmin($request, 'station');
        if (!$request->user()->hasRole('admin')) {
            if ($request->has('station_id') && (string) $request->input('station_id') !== (string) $request->user()->station_id) {
                abort(403, 'This account can only check in at its assigned station.');
            }
            $request->merge(['station_id' => $request->user()->station_id]);
        }
        $data = $request->validate([
            'rfid_uid' => ['required', 'string', 'max:64'],
            'station_id' => ['required', 'integer', 'exists:stations,id'],
        ]);
        // Keep desktop check-ins consistent with the physical station kiosks.
        $user = User::where('rfid_uid', trim($data['rfid_uid']))->first();
        abort_if($user && $user->hasAnyRole(['admin', 'staff']), 404);
        return app(StationController::class)->rfidTap($request);
    }

    public function rewardEligibility(Request $request)
    {
        $this->authorizeAdmin($request, 'station');
        $data = $request->validate([
            'rfid_uid' => ['required', 'string', 'max:64'],
        ]);
        $user = User::where('rfid_uid', trim($data['rfid_uid']))
            ->whereDoesntHave('roles', fn ($query) => $query->whereIn('name', ['admin', 'staff']))
            ->first();

        abort_unless($user, 404, 'RFID card not recognised');

        return response()->json(['data' => app(\App\Services\RewardEligibility::class)->forUser($user)]);
    }
}
