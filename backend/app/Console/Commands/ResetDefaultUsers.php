<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\User;

class ResetDefaultUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-default-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resets the default users values';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if (env('IS_DEMO')) {
            $adminRole = Role::where('name', 'System Administrator')->first();
            $user = User::where('email', 'admin@contrackpro.test')->first();

            if ($user) {
                $user->update([
                    'username' => 'admin@contrackpro.test',
                    'name' => 'ConTrackPro Administrator',
                    'email' => 'admin@contrackpro.test',
                    'position' => 'System Administrator',
                    'office_unit' => 'LGU Tuao - System Administration',
                    'role_id' => $adminRole?->id,
                    'is_active' => true,
                    'password' => 'password',
                ]);
            }
        }
    }
}
