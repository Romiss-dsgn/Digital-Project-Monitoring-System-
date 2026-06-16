<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'System Administrator')->first();

        User::updateOrCreate(
            ['email' => 'admin@contrackpro.test'],
            [
                'username' => 'admin@contrackpro.test',
                'name' => 'ConTrackPro Administrator',
                'badge_number' => 'BFP-ADMIN-0001',
                'contact_number' => null,
                'position' => 'System Administrator',
                'office_unit' => 'BFP Region II - System Administration',
                'role_id' => $adminRole?->id,
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => 'password',
            ]
        );
    }
}
