<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $credentials = config('seeding.admin');

        foreach (['email', 'code', 'password'] as $field) {
            if (!is_string($credentials[$field] ?? null) || trim($credentials[$field]) === '') {
                throw new RuntimeException('Set ADMIN_EMAIL, ADMIN_CODE, and ADMIN_PASSWORD before running AdminSeeder.');
            }
        }

        Role::findOrCreate('admin', 'web');

        $user = User::firstOrCreate([
            'email' => $credentials['email'],
        ], [
            'code' => $credentials['code'],
            'password' => Hash::make($credentials['password']),
        ]);

        $user->assignRole('admin');
    }
}
