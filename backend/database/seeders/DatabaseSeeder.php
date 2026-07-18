<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            RolesSeeder::class,
            RolePermissionsSeeder::class,
            UsersSeeder::class,
            ProjectsSeeder::class,
            ContractManagementSeeder::class,
            VariationOrdersSeeder::class,
            CashflowSeeder::class,
            ProjectAccomplishmentsSeeder::class,
            EngineeringPlansSeeder::class,
        ]);
    }
}
