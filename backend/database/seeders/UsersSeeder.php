<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::query()->pluck('id', 'name');
        $adminRoleId = $roles['System Administrator'] ?? null;

        $users = [
            [
                'email' => 'admin@contrackpro.test',
                'username' => 'admin@contrackpro.test',
                'name' => 'ConTrackPro Administrator',
                'badge_number' => 'BFP-QA-0001',
                'contact_number' => '639171000001',
                'position' => 'System Administrator',
                'office_unit' => 'BFP Region II - System Administration',
                'role_id' => $adminRoleId,
            ],
            [
                'email' => 'qa.engineer@contrackpro.test',
                'username' => 'qa.engineer@contrackpro.test',
                'name' => 'QA Planning Engineer',
                'badge_number' => 'BFP-QA-0002',
                'contact_number' => '639171000002',
                'position' => 'Planning Engineer',
                'office_unit' => 'BFP Region II - Engineering Planning Unit',
                'role_id' => $roles['Engineer - Planning'] ?? $adminRoleId,
            ],
            [
                'email' => 'qa.monitor@contrackpro.test',
                'username' => 'qa.monitor@contrackpro.test',
                'name' => 'QA Contract Monitor',
                'badge_number' => 'BFP-QA-0003',
                'contact_number' => '639171000003',
                'position' => 'Cashflow Monitoring Officer',
                'office_unit' => 'BFP Region II - Contract Monitoring Unit',
                'role_id' => $roles['Contract Monitoring Personnel'] ?? $adminRoleId,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user + [
                    'is_active' => true,
                    'last_active_at' => now(),
                    'password' => 'password',
                ]
            );
        }
    }
}
