<?php

$users = \App\Models\User::with('role')->get(['id', 'email', 'role_id']);

foreach ($users as $u) {
    echo str_pad($u->email, 40) . " | " . ($u->role?->name ?? 'NO ROLE') . PHP_EOL;
}