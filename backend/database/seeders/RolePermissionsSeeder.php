<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;

class RolePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'dashboard',
            'users',
            'roles',
            'access_requests',
            'contractors',
            'projects',
            'contracts',
            'contract_documents',
            'project_documents',
            'engineering_plans',
            'cashflow_periods',
            'invoices',
            'invoice_documents',
            'payments',
            'variation_orders',
            'contract_time_extensions',
            'work_suspensions',
            'project_accomplishments',
            'contractor_performance_ratings',
            'notifications',
            'audit_logs',
            'reports',
        ];

        $profiles = [
            'System Administrator' => $modules,
            'Administrative Staff - Contract Documentation' => [
                'dashboard',
                'contractors',
                'projects',
                'contracts',
                'contract_documents',
                'project_documents',
                'notifications',
                'reports',
            ],
            'Records Management Personnel' => [
                'dashboard',
                'contractors',
                'projects',
                'contracts',
                'contract_documents',
                'project_documents',
                'engineering_plans',
                'invoice_documents',
                'project_accomplishments',
                'notifications',
                'reports',
            ],
            'Contract Monitoring Personnel' => [
                'dashboard',
                'contractors',
                'projects',
                'contracts',
                'cashflow_periods',
                'invoices',
                'payments',
                'variation_orders',
                'contract_time_extensions',
                'work_suspensions',
                'project_accomplishments',
                'contractor_performance_ratings',
                'notifications',
                'reports',
            ],
            'Engineer - Planning' => [
                'dashboard',
                'projects',
                'project_documents',
                'engineering_plans',
                'notifications',
                'reports',
            ],
            'Engineer - Supervision' => [
                'dashboard',
                'projects',
                'contracts',
                'engineering_plans',
                'work_suspensions',
                'contract_time_extensions',
                'project_accomplishments',
                'notifications',
                'reports',
            ],
            'Engineer - Monitoring' => [
                'dashboard',
                'projects',
                'contracts',
                'variation_orders',
                'project_accomplishments',
                'contractor_performance_ratings',
                'notifications',
                'reports',
            ],
        ];

        foreach ($profiles as $roleName => $allowedModules) {
            $role = Role::where('name', $roleName)->first();

            if (! $role) {
                continue;
            }

            foreach ($modules as $module) {
                $enabled = in_array($module, $allowedModules, true);
                $isAdmin = $roleName === 'System Administrator';

                RolePermission::updateOrCreate(
                    [
                        'role_id' => $role->id,
                        'module' => $module,
                    ],
                    [
                        'can_view' => $enabled,
                        'can_create' => $isAdmin || $enabled,
                        'can_edit' => $isAdmin || $enabled,
                        'can_delete' => $isAdmin,
                        'can_approve' => $isAdmin || in_array($module, [
                            'access_requests',
                            'contracts',
                            'contract_documents',
                            'project_documents',
                            'engineering_plans',
                            'invoices',
                            'variation_orders',
                            'contract_time_extensions',
                            'project_accomplishments',
                        ], true),
                        'can_export' => $enabled,
                    ]
                );
            }
        }
    }
}
