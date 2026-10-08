<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
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

        if (!$user || !Hash::check($data['password'], $user->password) || !$user->hasRole('admin')) {
            throw ValidationException::withMessages(['email' => 'The provided admin credentials are invalid.']);
        }

        $expiresAt = now()->addDay();
        $token = $user->createToken($data['device_name'] ?? 'tauri-nfc', ['nfc:manage'], $expiresAt);

        return response()->json([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->toIso8601String(),
            'user' => $this->userData($user),
        ]);
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()->hasRole('admin') && $request->user()->tokenCan('nfc:manage'), 403);
    }

    private function userData(User $user): array
    {
        return [
            'id' => $user->id,
            'email' => $user->email,
            'code' => $user->code,
            'mobile_number' => $user->code,
            'rfid_uid' => $user->rfid_uid,
        ];
    }

    public function me(Request $request)
    {
        $this->authorizeAdmin($request);
        return response()->json(['data' => $this->userData($request->user())]);
    }

    public function users(Request $request)
    {
        $this->authorizeAdmin($request);
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'without_nfc' => ['sometimes', 'boolean'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);
        $query = User::whereDoesntHave('roles', fn ($query) => $query->where('name', 'admin'));
        if ($request->boolean('without_nfc')) {
            $query->where(fn ($query) => $query->whereNull('rfid_uid')->orWhere('rfid_uid', ''));
        }
        if (!empty($data['search'])) {
            $query->where(fn ($query) => $query->where('code', 'like', '%' . $data['search'] . '%')
                ->orWhere('id', $data['search']));
        }
        $users = $query->orderByDesc('id')->paginate($data['per_page'] ?? 25);
        $users->through(fn ($user) => $this->userData($user));
        return response()->json($users);
    }

    public function show(Request $request, User $user)
    {
        $this->authorizeAdmin($request);
        abort_if($user->hasRole('admin'), 404);
        return response()->json(['data' => $this->userData($user)]);
    }

    public function assign(Request $request, User $user)
    {
        $this->authorizeAdmin($request);
        abort_if($user->hasRole('admin'), 404);
        app(\App\Services\RfidAssignment::class)->assign($user, $request->input('rfid_uid'));
        return response()->json(['message' => 'NFC assigned successfully.', 'data' => $this->userData($user)]);
    }

    public function logout(Request $request)
    {
        $this->authorizeAdmin($request);
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }
}
