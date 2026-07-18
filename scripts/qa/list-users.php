<?php

use App\Models\User;

require __DIR__ . '/../../backend/vendor/autoload.php';

$app = require __DIR__ . '/../../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$users = User::with('role')
    ->orderBy('email')
    ->get(['id', 'email', 'role_id', 'is_active']);

foreach ($users as $user) {
    echo str_pad($user->email, 36)
        . ' | '
        . str_pad($user->role?->name ?? 'NO ROLE', 40)
        . ' | '
        . ($user->is_active ? 'active' : 'inactive')
        . PHP_EOL;
}
