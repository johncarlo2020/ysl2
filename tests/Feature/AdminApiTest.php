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

    public function test_user_search_pagination_filters_and_global_counts(): void
    {
        $admin = $this->account('list-admin', true);
        $staff = $this->account('list-staff');
        $staff->assignRole(Role::findOrCreate('staff', 'web'));
        $staff->forceFill(['staff_function' => 'register'])->save();
        $unassigned = $this->account('09170000001');
        $assigned = $this->account('09170000002');
        $assigned->update(['rfid_uid' => '00AB+&']);
        $redeemed = $this->account('09170000003');
        $legacyRedeemed = $this->account('09170000004');
        $legacyRedeemed->update(['rfid_uid' => 'OLD-CARD']);
        \Illuminate\Support\Facades\DB::table('stations')->insert(['id' => 4, 'name' => 'Reward', 'description' => 'Reward']);
        foreach ([$redeemed, $legacyRedeemed] as $user) {
            foreach ([4, 4] as $stationId) {
                \App\Models\StationUser::create(['user_id' => $user->id, 'station_id' => $stationId]);
            }
        }
        $counts = ['all' => 4, 'assigned' => 1, 'unassigned' => 1, 'redeemed' => 2];
        $this->withToken($admin->createToken('desktop', ['nfc:manage'])->plainTextToken);
        $first = $this->getJson('/api/admin/users?per_page=2&page=1')->assertOk()
            ->assertJsonCount(2, 'data')->assertJsonPath('total', 4)->assertJsonPath('last_page', 2)
            ->assertJsonPath('current_page', 1)->assertJsonPath('data.0.id', $legacyRedeemed->id)
            ->assertJsonPath('counts', $counts)->json('data');
        $second = $this->getJson('/api/admin/users?per_page=2&page=2')->assertOk()
            ->assertJsonCount(2, 'data')->assertJsonPath('current_page', 2)->json('data');
        $this->assertEmpty(array_intersect(array_column($first, 'id'), array_column($second, 'id')));
        foreach (['assigned' => [$assigned->id], 'unassigned' => [$unassigned->id], 'redeemed' => [$legacyRedeemed->id, $redeemed->id]] as $status => $ids) {
            $result = $this->getJson('/api/admin/users?' . http_build_query(['assignment_status' => $status]))
                ->assertOk()->assertJsonPath('total', count($ids))->assertJsonPath('counts', $counts);
            $this->assertSame($ids, array_column($result->json('data'), 'id'));
        }
        foreach (['000002', '00AB+&'] as $search) {
            $this->getJson('/api/admin/users?' . http_build_query(['search' => $search, 'per_page' => 1]))
                ->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.id', $assigned->id)
                ->assertJsonPath('counts', $counts);
        }
        $this->getJson('/api/admin/users?search=' . $assigned->id)->assertOk()->assertJsonFragment(['id' => $assigned->id]);
        $this->getJson('/api/admin/users?assignment_status=unassigned&search=000002')->assertOk()->assertJsonPath('total', 0);
        $this->getJson('/api/admin/users?search=missing')->assertOk()->assertJsonCount(0, 'data')->assertJsonPath('total', 0)->assertJsonPath('last_page', 1);
        $this->getJson('/api/admin/users?without_nfc=1')->assertOk()->assertJsonPath('total', 2);
        $this->getJson('/api/admin/users?search=0')->assertOk()->assertJsonPath('total', 4);
        $this->getJson('/api/admin/users?per_page=2&page=9')->assertOk()->assertJsonCount(0, 'data')->assertJsonPath('total', 4);
        $this->withToken($staff->createToken('desktop', ['nfc:manage'])->plainTextToken)
            ->getJson('/api/admin/users?assignment_status=assigned')->assertOk()->assertJsonPath('total', 1);
    }

    public function test_user_list_validates_server_side_list_parameters(): void
    {
        $admin = $this->account('validation-admin', true);
        $this->withToken($admin->createToken('desktop', ['nfc:manage'])->plainTextToken);
        foreach ([['assignment_status' => 'invalid'], ['page' => 0], ['page' => 'bad'], ['per_page' => 101], ['per_page' => 0], ['search' => str_repeat('x', 256)]] as $query) {
            $this->getJson('/api/admin/users?' . http_build_query($query))->assertUnprocessable()->assertJsonValidationErrors(array_keys($query));
        }
    }

    public function test_nfc_changes_broadcast_only_after_commit(): void
    {
        \Illuminate\Support\Facades\Event::fake([\App\Events\NfcAssignmentChanged::class]);
        $user = $this->account('live-nfc');
        $service = app(\App\Services\RfidAssignment::class);
        \Illuminate\Support\Facades\DB::beginTransaction();
        $service->assign($user, 'CARD-1');
        \Illuminate\Support\Facades\Event::assertNotDispatched(\App\Events\NfcAssignmentChanged::class);
        \Illuminate\Support\Facades\DB::commit();
        \Illuminate\Support\Facades\Event::assertDispatched(\App\Events\NfcAssignmentChanged::class,
            fn ($event) => $event->userId === $user->id && $event->hasNfc);
        $service->unassign($user);
        \Illuminate\Support\Facades\Event::assertDispatched(\App\Events\NfcAssignmentChanged::class,
            fn ($event) => $event->userId === $user->id && !$event->hasNfc);
        $this->assertNull($user->fresh()->rfid_uid);
    }

    public function test_rolled_back_card_assignment_does_not_broadcast(): void
    {
        \Illuminate\Support\Facades\Event::fake([\App\Events\NfcAssignmentChanged::class]);
        $user = $this->account('rollback-nfc');
        \Illuminate\Support\Facades\DB::beginTransaction();
        app(\App\Services\RfidAssignment::class)->assign($user, 'CARD-ROLLBACK');
        \Illuminate\Support\Facades\DB::rollBack();
        \Illuminate\Support\Facades\Event::assertNotDispatched(\App\Events\NfcAssignmentChanged::class);
        $this->assertNull($user->fresh()->rfid_uid);
    }

    public function test_live_state_returns_only_the_authenticated_users_progress(): void
    {
        $this->getJson('/user/live-state')->assertStatus(302);
        $user = $this->account('live-client');
        $user->assignRole(Role::findOrCreate('client', 'web'));
        $other = $this->account('other-client');
        \Illuminate\Support\Facades\DB::table('stations')->insert(['id' => 1, 'name' => 'Station 1', 'description' => 'Description']);
        \App\Models\StationUser::create(['user_id' => $other->id, 'station_id' => 1]);
        $this->actingAs($user)->getJson('/user/live-state')->assertOk()
            ->assertExactJson(['has_nfc' => false, 'completed_station_ids' => []]);
        $user->rfid_uid = 'LIVE-CARD';
        $user->save();
        \App\Models\StationUser::create(['user_id' => $user->id, 'station_id' => 1]);
        $this->getJson('/user/live-state')->assertOk()
            ->assertExactJson(['has_nfc' => true, 'completed_station_ids' => [1]]);
    }

    public function test_client_pages_render_the_shared_private_channel_listener(): void
    {
        $this->withoutVite();
        $user = $this->account('live-pages');
        $user->assignRole(Role::findOrCreate('client', 'web'));
        foreach (range(1, 4) as $id) {
            \Illuminate\Support\Facades\DB::table('stations')->insert([
                'id' => $id, 'name' => 'Station ' . $id, 'description' => 'Description',
            ]);
        }
        $this->actingAs($user);
        foreach (['/landing', '/dashboard', '/station/1'] as $url) {
            $response = $this->get($url)->assertOk()
                ->assertSee('private-user.' . $user->id, false)
                ->assertSee('nfc.assignment.changed', false)
                ->assertSee('pusher:subscription_succeeded', false);
            $this->assertSame(1, substr_count($response->getContent(), 'new Pusher('));
        }
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

    public function test_staff_login_token_is_valid_for_30_days(): void
    {
        $this->travelTo(now()->startOfSecond());
        $staff = $this->account('long-lived-staff');
        $staff->assignRole(Role::findOrCreate('staff', 'web'));
        $staff->forceFill(['staff_function' => 'register', 'station_id' => null])->save();
        $expiresAt = now()->addDays(30);
        $login = $this->postJson('/api/admin/login', [
            'email' => $staff->email, 'password' => 'secret-password',
        ])->assertOk()->assertJsonPath('expires_at', $expiresAt->toIso8601String());
        $this->assertTrue($staff->tokens()->firstOrFail()->expires_at->equalTo($expiresAt));
        $this->withToken($login->json('token'));
        $this->travelTo($expiresAt->copy()->subSecond());
        app('auth')->forgetGuards();
        $this->getJson('/api/admin/user')->assertOk();
        $this->travelTo($expiresAt->copy()->addSecond());
        app('auth')->forgetGuards();
        $this->getJson('/api/admin/user')->assertUnauthorized();
        $this->travelBack();
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

    public function test_rfid_lookup_returns_attendee_details_without_changing_assignment(): void
    {
        $admin = $this->account('admin', true);
        $client = $this->account('+639171234567');
        $client->update(['rfid_uid' => '001ABC']);
        $this->withToken($admin->createToken('desktop', ['nfc:manage'])->plainTextToken);

        $response = $this->getJson('/api/admin/users/by-rfid?' . http_build_query(['rfid_uid' => ' 001ABC ']));
        $response->assertOk()->assertExactJson(['data' => [
            'id' => $client->id,
            'email' => $client->email,
            'code' => $client->code,
            'mobile_number' => $client->code,
            'rfid_uid' => '001ABC',
            'role' => 'client',
            'staff_function' => null,
            'station_id' => null,
            'completed_station_ids' => [],
            'all_stations_completed' => false,
            'redeemed' => false,
            'can_link_nfc' => true,
        ]]);
        $this->assertSame('001ABC', $client->fresh()->rfid_uid);
        $this->assertDatabaseCount('station_users', 0);

        foreach ([[], ['rfid_uid' => ''], ['rfid_uid' => '   '], ['rfid_uid' => str_repeat('A', 65)], ['rfid_uid' => ['001ABC']]] as $query) {
            $this->getJson('/api/admin/users/by-rfid?' . http_build_query($query))
                ->assertUnprocessable()->assertJsonValidationErrors('rfid_uid');
        }
        $this->getJson('/api/admin/users/by-rfid?rfid_uid=unknown')->assertNotFound();
        $admin->update(['rfid_uid' => 'ADMINCARD']);
        $this->getJson('/api/admin/users/by-rfid?rfid_uid=ADMINCARD')->assertNotFound();
        $client->update(['rfid_uid' => null]);
        $this->getJson('/api/admin/users/by-rfid?rfid_uid=001ABC')->assertNotFound();
    }

    public function test_rfid_lookup_requires_authorized_token_and_supports_both_staff_functions(): void
    {
        $url = '/api/admin/users/by-rfid?rfid_uid=001ABC';
        $client = $this->account('client');
        $client->update(['rfid_uid' => '001ABC']);
        $this->getJson($url)->assertUnauthorized();
        $this->withToken($client->createToken('client', ['nfc:manage'])->plainTextToken);
        $this->getJson($url)->assertForbidden();

        $admin = $this->account('admin', true);
        app('auth')->forgetGuards();
        $this->withToken($admin->createToken('restricted', ['other'])->plainTextToken);
        $this->getJson($url)->assertForbidden();

        \Illuminate\Support\Facades\DB::table('stations')->insert(['id' => 1, 'name' => 'Station 1', 'description' => 'Description']);
        foreach (['register', 'station'] as $function) {
            $staff = $this->account($function);
            $staff->assignRole(Role::findOrCreate('staff', 'web'));
            $staff->staff_function = $function;
            $staff->station_id = $function === 'station' ? 1 : null;
            $staff->rfid_uid = strtoupper($function);
            $staff->save();
            app('auth')->forgetGuards();
            $this->withToken($staff->createToken('desktop', ['nfc:manage'])->plainTextToken);
            $this->getJson($url)->assertOk()->assertJsonPath('data.id', $client->id);
            $this->getJson('/api/admin/users/by-rfid?rfid_uid=' . $staff->rfid_uid)->assertNotFound();
            $staff->staff_function = null;
            $staff->save();
            app('auth')->forgetGuards();
            $this->getJson($url)->assertForbidden();
        }
    }

    public function test_reward_eligibility_and_final_station_release(): void
    {
        \Illuminate\Support\Facades\Event::fake([\App\Events\StationCheckedIn::class]);
        foreach (range(1, 4) as $id) {
            \Illuminate\Support\Facades\DB::table('stations')->insert(['id' => $id, 'name' => 'Station ' . $id, 'description' => 'Description']);
        }
        $client = $this->account('reward-client');
        $client->update(['rfid_uid' => '001ABC']);
        $staff = $this->account('reward-staff');
        $staff->assignRole(Role::findOrCreate('staff', 'web'));
        $staff->staff_function = 'station';
        $staff->station_id = 4;
        $staff->save();
        $this->withToken($staff->createToken('desktop', ['nfc:manage'])->plainTextToken);
        $url = '/api/admin/stations/reward-eligibility?rfid_uid=001ABC';
        $body = ['rfid_uid' => '001ABC'];

        $this->getJson($url)->assertOk()->assertJsonPath('data.eligible', false)
            ->assertJsonPath('data.missing_station_ids', [1, 2, 3]);
        foreach ([1, 1, 2] as $id) {
            \App\Models\StationUser::create(['user_id' => $client->id, 'station_id' => $id]);
        }
        $this->getJson($url)->assertOk()->assertExactJson(['data' => [
            'user_id' => $client->id, 'station_id' => 4, 'eligible' => false, 'redeemed' => false,
            'required_station_ids' => [1, 2, 3], 'completed_station_ids' => [1, 2], 'missing_station_ids' => [3],
        ]]);
        $this->assertDatabaseCount('station_users', 3);
        $this->assertSame('001ABC', $client->fresh()->rfid_uid);
        $this->postJson('/api/admin/stations/check-in', $body)->assertUnprocessable()
            ->assertJsonPath('status', 'prerequisites_not_met')->assertJsonPath('data.missing_station_ids', [3]);
        $this->assertDatabaseMissing('station_users', ['user_id' => $client->id, 'station_id' => 4]);
        $this->assertSame('001ABC', $client->fresh()->rfid_uid);

        \App\Models\StationUser::create(['user_id' => $client->id, 'station_id' => 3]);
        $this->getJson($url)->assertOk()->assertJsonPath('data.eligible', true)
            ->assertJsonPath('data.redeemed', false)->assertJsonPath('data.missing_station_ids', []);
        $this->assertSame('001ABC', $client->fresh()->rfid_uid);
        $this->postJson('/api/admin/stations/check-in', $body)->assertOk()->assertJsonPath('status', 'success');
        $this->assertNull($client->fresh()->rfid_uid);
        $this->getJson($url)->assertNotFound();
        $this->postJson('/api/admin/stations/check-in', $body)->assertNotFound();
        $this->assertDatabaseCount('station_users', 5);

        // A new card assignment must not make an already redeemed attendee eligible again.
        $client->refresh()->update(['rfid_uid' => '001ABC']);
        $this->getJson($url)->assertOk()->assertJsonPath('data.eligible', false)->assertJsonPath('data.redeemed', true);
        $this->postJson('/api/admin/stations/check-in', $body)->assertOk()->assertJsonPath('status', 'duplicate');
        $this->assertDatabaseCount('station_users', 5);
    }

    public function test_reward_eligibility_access_and_validation(): void
    {
        $url = '/api/admin/stations/reward-eligibility?rfid_uid=001ABC';
        $client = $this->account('reward-client');
        $client->update(['rfid_uid' => '001ABC']);
        $this->getJson($url)->assertUnauthorized();
        $this->withToken($client->createToken('client', ['nfc:manage'])->plainTextToken);
        $this->getJson($url)->assertForbidden();
        $register = $this->account('reward-register');
        $register->assignRole(Role::findOrCreate('staff', 'web'));
        $register->staff_function = 'register';
        $register->save();
        app('auth')->forgetGuards();
        $this->withToken($register->createToken('desktop', ['nfc:manage'])->plainTextToken);
        $this->getJson($url)->assertForbidden();
        $admin = $this->account('reward-admin', true);
        app('auth')->forgetGuards();
        $this->withToken($admin->createToken('restricted', ['other'])->plainTextToken);
        $this->getJson($url)->assertForbidden();
        app('auth')->forgetGuards();
        $this->withToken($admin->createToken('desktop', ['nfc:manage'])->plainTextToken);
        $this->getJson($url)->assertOk();
        $this->getJson('/api/admin/stations/reward-eligibility?' . http_build_query(['rfid_uid' => ' 001ABC ']))->assertOk();
        foreach ([[], ['rfid_uid' => '   '], ['rfid_uid' => str_repeat('A', 65)], ['rfid_uid' => ['001ABC']]] as $query) {
            $this->getJson('/api/admin/stations/reward-eligibility?' . http_build_query($query))
                ->assertUnprocessable()->assertJsonValidationErrors('rfid_uid');
        }
        $this->getJson('/api/admin/stations/reward-eligibility?rfid_uid=unknown')->assertNotFound();
        $admin->update(['rfid_uid' => 'ADMINCARD']);
        $register->update(['rfid_uid' => 'STAFFCARD']);
        foreach (['ADMINCARD', 'STAFFCARD'] as $uid) {
            $this->getJson('/api/admin/stations/reward-eligibility?rfid_uid=' . $uid)->assertNotFound();
        }
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

    public function test_unauthenticated_api_requests_without_json_accept_header_return_json(): void
    {
        $this->get('/api/admin/users')
            ->assertUnauthorized()
            ->assertHeader('content-type', 'application/json')
            ->assertExactJson(['message' => 'Unauthenticated.']);
    }

    public function test_failed_admin_api_requests_are_logged_without_secrets_or_query_values(): void
    {
        \Illuminate\Support\Facades\Log::spy();

        $this->withHeaders([
            'Authorization' => 'Bearer private-test-token',
            'CF-Ray' => 'test-ray-id',
        ])->getJson('/api/admin/users?search=private-search-value')
            ->assertUnauthorized();

        \Illuminate\Support\Facades\Log::shouldHaveReceived('log')
            ->once()
            ->with('warning', 'API request completed', \Mockery::on(function (array $context): bool {
                $this->assertSame('GET', $context['method']);
                $this->assertSame('api/admin/users', $context['route']);
                $this->assertSame(401, $context['status']);
                $this->assertNull($context['user_id']);
                $this->assertSame('test-ray-id', $context['cf_ray']);
                $this->assertIsInt($context['duration_ms']);
                $this->assertStringNotContainsString('private-test-token', json_encode($context));
                $this->assertStringNotContainsString('private-search-value', json_encode($context));

                return true;
            }));
    }

    public function test_successful_user_fetch_is_logged_for_api_endpoint(): void
    {
        \Illuminate\Support\Facades\Log::spy();
        $admin = $this->account('logged-fetch-admin', true);
        $token = $admin->createToken('desktop', ['nfc:manage'])->plainTextToken;

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'CF-Ray' => 'successful-fetch-ray',
        ])->getJson('/api/admin/users?search=private-search-value')
            ->assertOk();

        \Illuminate\Support\Facades\Log::shouldHaveReceived('log')
            ->once()
            ->with('info', 'API request completed', \Mockery::on(function (array $context) use ($admin, $token): bool {
                $this->assertSame('GET', $context['method']);
                $this->assertSame('api/admin/users', $context['route']);
                $this->assertSame(200, $context['status']);
                $this->assertSame($admin->id, $context['user_id']);
                $this->assertSame('successful-fetch-ray', $context['cf_ray']);
                $this->assertIsInt($context['duration_ms']);
                $this->assertStringNotContainsString($token, json_encode($context));
                $this->assertStringNotContainsString('private-search-value', json_encode($context));

                return true;
            }));
    }

    public function test_user_list_tracks_progress_and_redemption_after_card_release(): void
    {
        $admin = $this->account('progress-admin', true);
        $client = $this->account('progress-client');
        $this->withToken($admin->createToken('desktop', ['nfc:manage'])->plainTextToken);
        foreach (range(1, 4) as $id) {
            \Illuminate\Support\Facades\DB::table('stations')->insert(['id' => $id, 'name' => 'Station ' . $id, 'description' => 'Description']);
        }
        foreach ([1, 1, 2, 3] as $id) {
            \App\Models\StationUser::create(['user_id' => $client->id, 'station_id' => $id]);
        }
        $this->getJson('/api/admin/users')->assertOk()
            ->assertJsonPath('data.0.completed_station_ids', [1, 2, 3])
            ->assertJsonPath('data.0.all_stations_completed', false)
            ->assertJsonPath('data.0.redeemed', false)
            ->assertJsonPath('data.0.can_link_nfc', true);
        $this->putJson('/api/admin/users/' . $client->id . '/nfc', ['rfid_uid' => 'REDEEM-CARD'])->assertOk();
        \Illuminate\Support\Facades\Event::fake([\App\Events\StationCheckedIn::class, \App\Events\NfcAssignmentChanged::class]);
        $this->postJson('/api/admin/stations/check-in', ['rfid_uid' => 'REDEEM-CARD', 'station_id' => 4])->assertOk();
        $this->getJson('/api/admin/users')->assertOk()
            ->assertJsonPath('data.0.completed_station_ids', [1, 2, 3, 4])
            ->assertJsonPath('data.0.all_stations_completed', true)
            ->assertJsonPath('data.0.redeemed', true)
            ->assertJsonPath('data.0.can_link_nfc', false)
            ->assertJsonPath('data.0.rfid_uid', null);
        $this->putJson('/api/admin/users/' . $client->id . '/nfc', ['rfid_uid' => 'NEW-CARD'])
            ->assertUnprocessable()->assertJsonValidationErrors('rfid_uid');
        $this->assertNull($client->fresh()->rfid_uid);
    }

    public function test_redemption_blocks_linking_for_registration_staff_and_replacement_cards(): void
    {
        \Illuminate\Support\Facades\Event::fake([\App\Events\NfcAssignmentChanged::class]);
        $register = $this->account('redeemed-register');
        $register->assignRole(Role::findOrCreate('staff', 'web'));
        $register->forceFill(['staff_function' => 'register', 'station_id' => null])->save();
        $client = $this->account('already-redeemed');
        \Illuminate\Support\Facades\DB::table('stations')->insert(['id' => 4, 'name' => 'Station 4', 'description' => 'Description']);
        \App\Models\StationUser::create(['user_id' => $client->id, 'station_id' => 4]);
        $this->withToken($register->createToken('desktop', ['nfc:manage'])->plainTextToken);
        foreach ([null, 'LEGACY-CARD'] as $uid) {
            $client->update(['rfid_uid' => $uid]);
            $this->putJson('/api/admin/users/' . $client->id . '/nfc', ['rfid_uid' => 'RELINK-CARD'])
                ->assertUnprocessable()->assertJsonValidationErrors('rfid_uid');
            $this->assertSame($uid, $client->fresh()->rfid_uid);
        }
        $this->getJson('/api/admin/users')->assertOk()->assertJsonPath('data.0.redeemed', true);
        \Illuminate\Support\Facades\Event::assertNotDispatched(\App\Events\NfcAssignmentChanged::class);
    }
}
