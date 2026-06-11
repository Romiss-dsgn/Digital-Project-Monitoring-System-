<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'System Administrator' => 'Full access to user management, approvals, configuration, reports, and audit logs.',
            'Administrative Staff - Contract Documentation' => 'Maintains contract records, contract documents, and administrative submissions.',
            'Records Management Personnel' => 'Manages official project, contract, invoice, and accomplishment records.',
            'Contract Monitoring Personnel' => 'Monitors contract status, cashflow, invoices, variation orders, and contractor performance.',
            'Engineer - Planning' => 'Manages infrastructure project planning records and engineering plan submissions.',
            'Engineer - Supervision' => 'Updates site supervision records, accomplishments, suspensions, and time extension details.',
            'Engineer - Monitoring' => 'Reviews implementation progress, timelines, accomplishments, and performance indicators.',
        ];

        foreach ($roles as $name => $description) {
            Role::updateOrCreate(
                ['name' => $name],
                ['description' => $description]
            );
        }
    }
}
