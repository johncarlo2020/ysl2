<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        foreach ([
            'database/migrations/2014_10_12_000000_create_users_table.php',
            'database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php',
            'database/migrations/2024_07_16_061509_create_permission_tables.php',
            'database/migrations/2026_04_28_000000_add_rfid_uid_to_users_table.php',
        ] as $path) {
            $this->artisan('migrate', ['--path' => $path, '--force' => true]);
        }
    }

    private function account(string $code, bool $admin = false): User
    {
        $user = User::create(['code' => $code, 'email' => $code . '@example.com', 'password' => Hash::make('secret-password')]);
        if ($admin) {
            $user->assignRole(Role::findOrCreate('admin', 'web'));
        }
        return $user;
    }

    public function test_admin_can_login_get_users_assign_nfc_and_revoke_token(): void
    {
        $admin = $this->account('admin', true);
        $client = $this->account('+639171234567');
        $login = $this->postJson('/api/admin/login', ['email' => $admin->email, 'password' => 'secret-password']);
        $login->assertOk()->assertJsonStructure(['token', 'expires_at']);
        $this->withHeader('Authorization', 'Bearer ' . $login->json('token'));
        $this->getJson('/api/admin/user')->assertOk()->assertJsonPath('data.id', $admin->id);
        $this->getJson('/api/admin/users?without_nfc=1')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/admin/users/' . $client->id)->assertOk()->assertJsonPath('data.mobile_number', $client->code);
        $this->putJson('/api/admin/users/' . $client->id . '/nfc', ['rfid_uid' => 'ABC123'])->assertOk();
        $this->assertDatabaseHas('users', ['id' => $client->id, 'rfid_uid' => 'ABC123']);
        $other = $this->account('other');
        $this->putJson('/api/admin/users/' . $other->id . '/nfc', ['rfid_uid' => 'ABC123'])->assertUnprocessable();
        $this->putJson('/api/admin/users/' . $admin->id . '/nfc', ['rfid_uid' => 'ADMIN'])->assertNotFound();
        $this->postJson('/api/admin/logout')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_invalid_and_non_admin_credentials_cannot_login(): void
    {
        $client = $this->account('client');
        $this->postJson('/api/admin/login', ['email' => $client->email, 'password' => 'secret-password'])->assertUnprocessable();
        $admin = $this->account('admin', true);
        $this->postJson('/api/admin/login', ['email' => $admin->email, 'password' => 'wrong'])->assertUnprocessable();
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->getJson('/api/admin/users')->assertUnauthorized();
        $token = $client->createToken('client', ['nfc:manage'])->plainTextToken;
        $this->withHeader('Authorization', 'Bearer ' . $token)->getJson('/api/admin/users')->assertForbidden();
    }
}
