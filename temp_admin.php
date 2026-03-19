<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::firstOrNew(['email' => 'scxipted@gmail.com']);
$user->name = 'Scxipted';
$user->surname = 'Admin';
if (!$user->exists) {
    $user->password = Hash::make('admin123');
}
$user->role = User::ROLE_ADMIN;
$user->save();

echo "User " . $user->email . " is now an admin.";
