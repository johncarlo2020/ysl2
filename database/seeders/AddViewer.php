<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Station;
use App\Models\Regime;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class AddViewer extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'fname' => 'viewer',
            'lname' => 'viewer',
            'age_group' => 'viewer',
            'number' => '0123456789',
            'email' => 'viewer@gmail.com',
            'country' => 'Malaysia',
            'password' => Hash::make('WowsomeYslViewer'),
        ]);

        $permission = Permission::create(['name' => 'view']);
        $permission = Permission::create(['name' => 'full']);

        $user->assignRole('admin');

        $user->givePermissionTo('view');

        $user2 = User::find(1);
        $user2->givePermissionTo('full');
    }
}
