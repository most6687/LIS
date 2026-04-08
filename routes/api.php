<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ActivityLogController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', function (Request $request) {
        return $request->user();
    });

});


/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'role:Admin'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    */

    Route::get('/users', [UserController::class, 'index'])
        ->middleware('permission:view_users');

    Route::post('/users', [UserController::class, 'store'])
        ->middleware('permission:create_users');

    Route::put('/users/{id}', [UserController::class, 'update'])
        ->middleware('permission:update_users');

    Route::delete('/users/{id}', [UserController::class, 'destroy'])
        ->middleware('permission:delete_users');


    /*
    |--------------------------------------------------------------------------
    | Roles
    |--------------------------------------------------------------------------
    */

    Route::get('/roles', [RoleController::class, 'index'])
        ->middleware('permission:view_roles');

    Route::get('/roles/{id}', [RoleController::class, 'show'])
        ->middleware('permission:view_roles');

    Route::post('/roles', [RoleController::class, 'store'])
        ->middleware('permission:create_roles');

    Route::put('/roles/{id}', [RoleController::class, 'update'])
        ->middleware('permission:update_roles');

    Route::delete('/roles/{id}', [RoleController::class, 'destroy'])
        ->middleware('permission:delete_roles');


    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    */

    Route::get('/permissions', [PermissionController::class, 'index'])
        ->middleware('permission:view_permissions');

    Route::post('/permissions', [PermissionController::class, 'store'])
        ->middleware('permission:create_permissions');

    Route::get('/permissions/{id}', [PermissionController::class, 'show'])
        ->middleware('permission:view_permissions');

    Route::put('/permissions/{id}', [PermissionController::class, 'update'])
        ->middleware('permission:update_permissions');

    Route::delete('/permissions/{id}', [PermissionController::class, 'destroy'])
        ->middleware('permission:delete_permissions');


    /*
    |--------------------------------------------------------------------------
    | Assign Permission To Role
    |--------------------------------------------------------------------------
    */

    Route::post('/assign-permission', [PermissionController::class, 'assignPermissionToRole']);


    /*
    |--------------------------------------------------------------------------
    | Activity Logs
    |--------------------------------------------------------------------------
    */

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])
        ->middleware('permission:view_logs');

});


/*
|--------------------------------------------------------------------------
| Patients (Admin + Receptionist)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'role:Admin,Receptionist'])->group(function () {

    Route::get('/patients', [PatientController::class, 'index'])
        ->middleware('permission:view_patients');

    Route::post('/patients', [PatientController::class, 'store'])
        ->middleware('permission:create_patient');

    Route::put('/patients/{id}', [PatientController::class, 'update'])
        ->middleware('permission:update_patient');

    Route::delete('/patients/{id}', [PatientController::class, 'destroy'])
        ->middleware('permission:delete_patient');

});