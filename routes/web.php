<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'system' => 'Laboratory Information System',
        'version' => '1.0',
        'status' => 'API Running',
        'developer' => 'Backend Team 1',
        'endpoints' => [
            'login' => '/api/login',
            'logout' => '/api/logout',
            'users' => '/api/users',
            'patients' => '/api/patients',
            'activity_logs' => '/api/activity-logs'
        ]
    ]);
});