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
        $roles = Role::query()->pluck('id', 'name');

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
                'password' => 'password',
            ]
        );

        $users = [
            ['Maria Santos', 'maria.santos@bfp.gov.ph', 'BFP-2026-0101', 'Administrative Staff - Contract Documentation', 'Contract Records Officer', 'Contract Documentation Unit'],
            ['Jose Dela Cruz', 'jose.delacruz@bfp.gov.ph', 'BFP-2026-0102', 'Records Management Personnel', 'Records Custodian', 'Records Management Section'],
            ['Ana Reyes', 'ana.reyes@bfp.gov.ph', 'BFP-2026-0103', 'Contract Monitoring Personnel', 'Contract Monitoring Officer', 'Contract Monitoring Unit'],
            ['Mark Villanueva', 'mark.villanueva@bfp.gov.ph', 'BFP-2026-0104', 'Engineer - Planning', 'Planning Engineer', 'Engineering Planning Unit'],
            ['Grace Lim', 'grace.lim@bfp.gov.ph', 'BFP-2026-0105', 'Engineer - Supervision', 'Site Supervision Engineer', 'Engineering Supervision Unit'],
            ['Paolo Garcia', 'paolo.garcia@bfp.gov.ph', 'BFP-2026-0106', 'Engineer - Monitoring', 'Project Monitoring Engineer', 'Engineering Monitoring Unit'],
            ['Liza Mendoza', 'liza.mendoza@bfp.gov.ph', 'BFP-2026-0107', 'Administrative Staff - Contract Documentation', 'Procurement Records Assistant', 'Administrative Services'],
            ['Carlo Ramos', 'carlo.ramos@bfp.gov.ph', 'BFP-2026-0108', 'Contract Monitoring Personnel', 'Cashflow Monitoring Officer', 'Finance and Contract Monitoring'],
            ['Nina Flores', 'nina.flores@bfp.gov.ph', 'BFP-2026-0109', 'Records Management Personnel', 'Document Control Officer', 'Records Management Section'],
            ['Erwin Cruz', 'erwin.cruz@bfp.gov.ph', 'BFP-2026-0110', 'Engineer - Planning', 'Infrastructure Planning Engineer', 'Engineering Planning Unit'],
            ['Rhea Bautista', 'rhea.bautista@bfp.gov.ph', 'BFP-2026-0111', 'Engineer - Supervision', 'Construction Supervision Engineer', 'Field Operations'],
            ['Daniel Navarro', 'daniel.navarro@bfp.gov.ph', 'BFP-2026-0112', 'Engineer - Monitoring', 'Progress Monitoring Officer', 'Project Monitoring Unit'],
            ['Camille Torres', 'camille.torres@bfp.gov.ph', 'BFP-2026-0113', 'Contract Monitoring Personnel', 'Variation Order Reviewer', 'Contract Monitoring Unit'],
            ['Miguel Rivera', 'miguel.rivera@bfp.gov.ph', 'BFP-2026-0114', 'Records Management Personnel', 'Audit Records Officer', 'Audit Documentation Desk'],
            ['Sofia Castillo', 'sofia.castillo@bfp.gov.ph', 'BFP-2026-0115', 'Administrative Staff - Contract Documentation', 'Contract Documentation Assistant', 'Contract Documentation Unit'],
            ['Adrian Gonzales', 'adrian.gonzales@bfp.gov.ph', 'BFP-2026-0116', 'Engineer - Supervision', 'Accomplishment Validation Engineer', 'Engineering Supervision Unit'],
            ['Joanna Aquino', 'joanna.aquino@bfp.gov.ph', 'BFP-2026-0117', 'Engineer - Monitoring', 'Contractor Performance Analyst', 'Project Monitoring Unit'],
            ['Ryan Mercado', 'ryan.mercado@bfp.gov.ph', 'BFP-2026-0118', 'Contract Monitoring Personnel', 'Invoice Monitoring Officer', 'Finance and Contract Monitoring'],
            ['Patricia Uy', 'patricia.uy@bfp.gov.ph', 'BFP-2026-0119', 'Engineer - Planning', 'Engineering Plan Reviewer', 'Engineering Planning Unit'],
            ['Kevin Tan', 'kevin.tan@bfp.gov.ph', 'BFP-2026-0120', 'Records Management Personnel', 'Digital Records Encoder', 'Records Management Section'],
        ];

        foreach ($users as [$name, $email, $badgeNumber, $roleName, $position, $officeUnit]) {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'username' => $email,
                    'name' => $name,
                    'badge_number' => $badgeNumber,
                    'contact_number' => '09' . fake()->unique()->numerify('#########'),
                    'position' => $position,
                    'office_unit' => "BFP Region II - {$officeUnit}",
                    'role_id' => $roles[$roleName] ?? $adminRole?->id,
                    'is_active' => true,
                    'last_active_at' => now(),
                    'password' => 'password',
                ]
            );
        }
    }
}
