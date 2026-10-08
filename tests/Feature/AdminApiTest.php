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
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        foreach ([
            'database/migrations/2014_10_12_000000_create_users_table.php',
            'database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php',
            'database/migrations/2024_07_16_061509_create_permission_tables.php',
            'database/migrations/2026_04_28_000000_add_rfid_uid_to_users_table.php',
            'database/migrations/2024_05_09_152825_create_stations_table.php',
            'database/migrations/2024_05_16_041232_create_station_users_table.php',
            'database/migrations/2026_10_08_000000_add_staff_fields_to_users_table.php',
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

    public function test_admin_can_manage_staff_with_validation_and_token_revocation(): void
    {
        $this->get('/admin/staff')->assertRedirect('/admin/login');
        $client = $this->account('client');
        $this->actingAs($client)->get('/admin/staff')->assertRedirect('/admin/login');
        $admin = $this->account('admin', true);
        $this->actingAs($admin);
        \Illuminate\Support\Facades\DB::table('stations')->insert(['id' => 1, 'name' => 'Station 1', 'description' => 'Description']);
        $this->get('/admin/staff')->assertOk()->assertSee('Staff users')->assertSee('staff-dialog')->assertSee('staff-table');
        $this->get('/admin/staff/create')->assertOk();
        $body = ['email' => 'newstaff@example.com', 'staff_function' => 'station', 'password' => 'staff-password-123', 'password_confirmation' => 'staff-password-123'];
        $this->post('/admin/staff', $body)->assertSessionHasErrors('station_id');
        $this->post('/admin/staff', $body + ['station_id' => 1])->assertRedirect(route('staff.index'));
        $staff = User::where('email', $body['email'])->firstOrFail();
        $this->assertTrue($staff->hasRole('staff'));
        $this->assertTrue(Hash::check($body['password'], $staff->password));
        $this->get('/admin/staff/' . $staff->id . '/edit')->assertOk()->assertSee($staff->email);
        $staff->createToken('desktop', ['nfc:manage']);
        $oldHash = $staff->password;
        $this->put('/admin/staff/' . $staff->id, ['email' => $staff->email, 'staff_function' => 'register'])->assertRedirect(route('staff.index'));
        $this->assertNull($staff->fresh()->station_id);
        $this->assertSame($oldHash, $staff->fresh()->password);
        $this->assertSame(0, $staff->tokens()->count());
        $this->put('/admin/staff/' . $staff->id, ['email' => $client->email, 'staff_function' => 'register'])->assertSessionHasErrors('email');
        $this->put('/admin/staff/' . $staff->id, ['email' => $staff->email, 'staff_function' => 'register', 'password' => 'replacement-password', 'password_confirmation' => 'replacement-password'])->assertRedirect(route('staff.index'));
        $this->assertTrue(Hash::check('replacement-password', $staff->fresh()->password));
        $this->postJson('/admin/staff', ['email' => 'modal@example.com', 'staff_function' => 'register', 'password' => 'modal-password-123', 'password_confirmation' => 'modal-password-123'])->assertOk()->assertJsonPath('message', 'Staff account created.');
        $this->postJson('/admin/staff', ['email' => 'modal@example.com', 'staff_function' => 'register'])->assertUnprocessable()->assertJsonValidationErrors(['email', 'password']);
        $this->get('/admin/staff/' . $client->id . '/edit')->assertNotFound();
        $this->put('/admin/staff/' . $admin->id, ['email' => $admin->email, 'staff_function' => 'register'])->assertNotFound();
    }

    public function test_staff_login_permissions_and_seeder(): void
    {
        \Illuminate\Support\Facades\Event::fake([\App\Events\StationCheckedIn::class]);
        foreach (range(1, 4) as $id) {
            \Illuminate\Support\Facades\DB::table('stations')->insert(['id' => $id, 'name' => 'Station ' . $id, 'description' => 'Description']);
        }
        $previousPassword = getenv('TAURI_STAFF_PASSWORD');
        $previousEnvPassword = $_ENV['TAURI_STAFF_PASSWORD'] ?? null;
        $previousServerPassword = $_SERVER['TAURI_STAFF_PASSWORD'] ?? null;
        $_ENV['TAURI_STAFF_PASSWORD'] = $_SERVER['TAURI_STAFF_PASSWORD'] = 'test-staff-password';
        putenv('TAURI_STAFF_PASSWORD=test-staff-password');
        try {
            $this->seed(\Database\Seeders\TauriStaffSeeder::class);
            $hash = User::where('email', 'registration1@staff.ysl.local')->first()->password;
            $this->seed(\Database\Seeders\TauriStaffSeeder::class);
            $this->assertSame($hash, User::where('email', 'registration1@staff.ysl.local')->first()->password);
        } finally {
            if ($previousEnvPassword === null) { unset($_ENV['TAURI_STAFF_PASSWORD']); } else { $_ENV['TAURI_STAFF_PASSWORD'] = $previousEnvPassword; }
            if ($previousServerPassword === null) { unset($_SERVER['TAURI_STAFF_PASSWORD']); } else { $_SERVER['TAURI_STAFF_PASSWORD'] = $previousServerPassword; }
            putenv($previousPassword === false ? 'TAURI_STAFF_PASSWORD' : 'TAURI_STAFF_PASSWORD=' . $previousPassword);
        }
        $this->assertSame(6, User::role('staff')->count());
        $client = $this->account('attendee');
        $login = $this->postJson('/api/admin/login', ['email' => 'registration1@staff.ysl.local', 'password' => 'test-staff-password']);
        $login->assertOk()->assertJsonPath('user.staff_function', 'register')->assertJsonPath('user.station_id', null);
        $this->withToken($login->json('token'));
        $this->getJson('/api/admin/users')->assertOk()->assertJsonCount(1, 'data');
        $this->putJson('/api/admin/users/' . $client->id . '/nfc', ['rfid_uid' => 'STAFFTEST'])->assertOk();
        $this->getJson('/api/admin/users?search=STAFFTEST')->assertOk()->assertJsonPath('data.0.id', $client->id);
        $this->deleteJson('/api/admin/users/' . $client->id . '/nfc')->assertOk();
        $this->putJson('/api/admin/users/' . $client->id . '/nfc', ['rfid_uid' => 'STAFFTEST'])->assertOk();
        $staff = User::where('email', 'station1@staff.ysl.local')->first();
        $this->putJson('/api/admin/users/' . $staff->id . '/nfc', ['rfid_uid' => 'NO'])->assertNotFound();
        $this->getJson('/api/admin/stations')->assertForbidden();
        $this->postJson('/api/admin/stations/check-in', ['rfid_uid' => 'STAFFTEST', 'station_id' => 1])->assertForbidden();
        $this->postJson('/api/admin/logout')->assertOk();
        app('auth')->forgetGuards();
        $this->flushHeaders();
        $login = $this->postJson('/api/admin/login', ['email' => $staff->email, 'password' => 'test-staff-password']);
        $login->assertOk()->assertJsonPath('user.staff_function', 'station')->assertJsonPath('user.station_id', 1);
        $this->withToken($login->json('token'));
        $this->getJson('/api/admin/user')->assertOk();
        $this->getJson('/api/admin/stations')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', 1);
        $this->getJson('/api/admin/users')->assertForbidden();
        $this->putJson('/api/admin/users/' . $client->id . '/nfc', ['rfid_uid' => 'NO'])->assertForbidden();
        $this->deleteJson('/api/admin/users/' . $client->id . '/nfc')->assertForbidden();
        $this->postJson('/api/admin/stations/check-in', ['rfid_uid' => 'STAFFTEST', 'station_id' => 2])->assertForbidden();
        $this->postJson('/api/admin/stations/check-in', ['rfid_uid' => 'STAFFTEST'])->assertOk()->assertJsonPath('status', 'success');
        $this->assertDatabaseHas('station_users', ['user_id' => $client->id, 'station_id' => 1]);
        $this->postJson('/api/admin/logout')->assertOk();
    }

    public function test_station_check_in_preserves_kiosk_rules(): void
    {
        \Illuminate\Support\Facades\Event::fake([\App\Events\StationCheckedIn::class]);
        foreach (range(1, 4) as $id) {
            \Illuminate\Support\Facades\DB::table('stations')->insert(['id' => $id, 'name' => 'Station ' . $id, 'description' => 'Description']);
        }
        $client = $this->account('client');
        $client->update(['rfid_uid' => 'ABC123']);
        $body = ['rfid_uid' => 'ABC123', 'station_id' => 1];
        $this->getJson('/api/admin/stations')->assertUnauthorized();
        $this->postJson('/api/admin/stations/check-in', $body)->assertUnauthorized();
        $this->withToken($client->createToken('client', ['nfc:manage'])->plainTextToken);
        $this->postJson('/api/admin/stations/check-in', $body)->assertForbidden();
        $admin = $this->account('admin', true);
        app('auth')->forgetGuards();
        $this->withToken($admin->createToken('desktop', ['nfc:manage'])->plainTextToken);
        $this->getJson('/api/admin/stations')->assertOk()->assertJsonCount(4, 'data');
        $this->postJson('/api/admin/stations/check-in', ['rfid_uid' => 'unknown', 'station_id' => 1])->assertNotFound();
        $this->postJson('/api/admin/stations/check-in', ['rfid_uid' => 'ABC123', 'station_id' => 99])->assertUnprocessable();
        $this->postJson('/api/admin/stations/check-in', ['rfid_uid' => 'ABC123', 'station_id' => 4])->assertUnprocessable()->assertJsonPath('status', 'prerequisites_not_met');
        $this->postJson('/api/admin/stations/check-in', $body)->assertOk()->assertJsonPath('status', 'success');
        $this->postJson('/api/admin/stations/check-in', $body)->assertOk()->assertJsonPath('status', 'duplicate');
        $this->assertDatabaseCount('station_users', 1);
        foreach ([2, 3, 4] as $id) {
            $this->postJson('/api/admin/stations/check-in', ['rfid_uid' => 'ABC123', 'station_id' => $id])->assertOk()->assertJsonPath('status', 'success');
        }
        $this->assertDatabaseCount('station_users', 4);
        $this->assertNull($client->fresh()->rfid_uid);
        \Illuminate\Support\Facades\Event::assertDispatched(\App\Events\StationCheckedIn::class, 4);
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
        $this->deleteJson('/api/admin/users/' . $admin->id . '/nfc')->assertNotFound();
        $this->deleteJson('/api/admin/users/' . $client->id . '/nfc')->assertOk()->assertJsonPath('data.rfid_uid', null);
        $this->assertDatabaseHas('users', ['id' => $client->id, 'rfid_uid' => null]);
        $this->deleteJson('/api/admin/users/' . $client->id . '/nfc')->assertOk();
        $this->putJson('/api/admin/users/' . $other->id . '/nfc', ['rfid_uid' => 'ABC123'])->assertOk();
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
        $this->deleteJson('/api/admin/users/' . $client->id . '/nfc')->assertUnauthorized();
        $token = $client->createToken('client', ['nfc:manage'])->plainTextToken;
        $this->withHeader('Authorization', 'Bearer ' . $token)->getJson('/api/admin/users')->assertForbidden();
        $this->deleteJson('/api/admin/users/' . $client->id . '/nfc')->assertForbidden();
    }
}
