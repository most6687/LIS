<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/create-admin', function () {
    \App\Models\User::where('Username', 'admin')->forceDelete();
    
    $user = \App\Models\User::create([
        'Username' => 'admin',
        'Password' => bcrypt('123456'),
        'Role' => 'Admin',
        'Role_ID' => 1,
        'Full_Name' => 'System Administrator',
        'Email' => 'admin@lis.com',
        'Is_Active' => true,
    ]);
    
    return response()->json([
        'success' => true,
        'username' => $user->Username,
        'password' => '123456',
    ]);
});