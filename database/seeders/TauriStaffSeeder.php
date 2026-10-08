<?php

namespace Database\Seeders;

use App\Models\Station;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TauriStaffSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('TAURI_STAFF_PASSWORD');
        if (!is_string($password) || strlen($password) < 12) {
            throw new \RuntimeException('Set TAURI_STAFF_PASSWORD to at least 12 characters before seeding.');
        }
        if (Station::whereIn('id', [1, 2, 3, 4])->count() !== 4) {
            throw new \RuntimeException('Seed stations 1–4 before running TauriStaffSeeder.');
        }

        DB::transaction(function () use ($password) {
            $role = Role::findOrCreate('staff', 'web');
            foreach (['registration1', 'registration2', 'station1', 'station2', 'station3', 'station4'] as $name) {
                $station = str_starts_with($name, 'station') ? (int) substr($name, -1) : null;
                $email = $name . '@staff.ysl.local';
                $existing = User::where('email', $email)->first();
                if ($existing && !$existing->hasRole('staff')) {
                    throw new \RuntimeException('Staff email already belongs to another account: ' . $email);
                }
                $user = User::firstOrCreate(['email' => $email], [
                    'code' => 'tauri-' . $name,
                    'password' => Hash::make($password),
                ]);
                $user->forceFill(['staff_function' => $station ? 'station' : 'register', 'station_id' => $station])->save();
                $user->assignRole($role);
            }
        });
    }
}
