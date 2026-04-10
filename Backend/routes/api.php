<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\DoctorController;
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
    
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    
    Route::get('/roles', [RoleController::class, 'index']);
    Route::get('/roles/{id}', [RoleController::class, 'show']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::put('/roles/{id}', [RoleController::class, 'update']);
    Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
    
    Route::get('/permissions', [PermissionController::class, 'index']);
    Route::post('/permissions', [PermissionController::class, 'store']);
    Route::get('/permissions/{id}', [PermissionController::class, 'show']);
    Route::put('/permissions/{id}', [PermissionController::class, 'update']);
    Route::delete('/permissions/{id}', [PermissionController::class, 'destroy']);
    
    Route::post('/assign-permission', [PermissionController::class, 'assignPermissionToRole']);
    
    Route::get('/doctors', [DoctorController::class, 'index']);
    Route::post('/doctors', [DoctorController::class, 'store']);
    Route::get('/doctors/{id}', [DoctorController::class, 'show']);
    Route::put('/doctors/{id}', [DoctorController::class, 'update']);
    Route::delete('/doctors/{id}', [DoctorController::class, 'destroy']);
    
    Route::get('/activity-logs', [ActivityLogController::class, 'index']);
});

/*
|--------------------------------------------------------------------------
| Patients Routes (Admin + Receptionist)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:Admin,Receptionist'])->group(function () {
    Route::get('/patients', [PatientController::class, 'index']);
    Route::post('/patients', [PatientController::class, 'store']);
    Route::get('/patients/{id}', [PatientController::class, 'show']);
    Route::put('/patients/{id}', [PatientController::class, 'update']);
    Route::delete('/patients/{id}', [PatientController::class, 'destroy']);
});


/*
|--------------------------------------------------------------------------
| Orders Routes (Admin + Receptionist)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:Admin,Receptionist'])->group(function () {
    Route::get('/orders', [App\Http\Controllers\Api\TestController::class, 'index']);
    Route::get('/orders/status/{status}', [App\Http\Controllers\Api\TestController::class, 'byStatus']);
    Route::get('/orders/urgent', [App\Http\Controllers\Api\TestController::class, 'urgent']);
    Route::get('/orders/patient/{patientId}', [App\Http\Controllers\Api\TestController::class, 'byPatient']);
    Route::get('/orders/{id}', [App\Http\Controllers\Api\TestController::class, 'show']);
    Route::post('/orders', [App\Http\Controllers\Api\TestController::class, 'store']);
    Route::put('/orders/{id}/status', [App\Http\Controllers\Api\TestController::class, 'updateStatus']);
    Route::put('/orders/{id}/priority', [App\Http\Controllers\Api\TestController::class, 'updatePriority']);
    Route::put('/orders/{id}/amount', [App\Http\Controllers\Api\TestController::class, 'updateTotalAmount']);
    Route::delete('/orders/{id}', [App\Http\Controllers\Api\TestController::class, 'destroy']);
});


/*
|--------------------------------------------------------------------------
| Reports Routes (Admin + Receptionist + Technician)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:Admin,Receptionist,Technician'])->group(function () {
    Route::get('/reports', [App\Http\Controllers\Api\ReportController::class, 'index']);
    Route::get('/reports/status/{status}', [App\Http\Controllers\Api\ReportController::class, 'byStatus']);
    Route::get('/reports/type/{type}', [App\Http\Controllers\Api\ReportController::class, 'byType']);
    Route::get('/reports/patient/{patientId}', [App\Http\Controllers\Api\ReportController::class, 'byPatient']);
    Route::get('/reports/{id}', [App\Http\Controllers\Api\ReportController::class, 'show']);
    Route::post('/reports', [App\Http\Controllers\Api\ReportController::class, 'store']);
    Route::put('/reports/{id}', [App\Http\Controllers\Api\ReportController::class, 'update']);
    Route::put('/reports/{id}/finalize', [App\Http\Controllers\Api\ReportController::class, 'finalize']);
    Route::post('/reports/{id}/upload', [App\Http\Controllers\Api\ReportController::class, 'uploadFile']);
    Route::get('/reports/{id}/download', [App\Http\Controllers\Api\ReportController::class, 'download']);
    Route::delete('/reports/{id}', [App\Http\Controllers\Api\ReportController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Billing Routes (Admin + Billing)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:Admin,Billing'])->group(function () {
    Route::get('/payments', [App\Http\Controllers\Api\BillingController::class, 'index']);
    Route::get('/payments/status/{status}', [App\Http\Controllers\Api\BillingController::class, 'byStatus']);
    Route::get('/payments/method/{method}', [App\Http\Controllers\Api\BillingController::class, 'byMethod']);
    Route::get('/payments/overdue', [App\Http\Controllers\Api\BillingController::class, 'overdue']);
    Route::get('/payments/patient/{patientId}', [App\Http\Controllers\Api\BillingController::class, 'byPatient']);
    Route::get('/payments/order/{orderId}', [App\Http\Controllers\Api\BillingController::class, 'byOrder']);
    Route::get('/payments/{id}', [App\Http\Controllers\Api\BillingController::class, 'show']);
    Route::post('/payments', [App\Http\Controllers\Api\BillingController::class, 'store']);
    Route::put('/payments/{id}', [App\Http\Controllers\Api\BillingController::class, 'update']);
    Route::post('/payments/{id}/add', [App\Http\Controllers\Api\BillingController::class, 'addPayment']);
    Route::put('/payments/{id}/status', [App\Http\Controllers\Api\BillingController::class, 'updateStatus']);
    Route::delete('/payments/{id}', [App\Http\Controllers\Api\BillingController::class, 'destroy']);
    Route::get('/payments/statistics/dashboard', [App\Http\Controllers\Api\BillingController::class, 'statistics']);
});


/*
|--------------------------------------------------------------------------
| Technician Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:Technician'])->group(function () {
    // Samples
    Route::get('/samples', [App\Http\Controllers\Api\SampleController::class, 'index']);
    Route::get('/samples/pending', [App\Http\Controllers\Api\SampleController::class, 'pending']);
    Route::get('/samples/{id}', [App\Http\Controllers\Api\SampleController::class, 'show']);
    Route::put('/samples/{id}/status', [App\Http\Controllers\Api\SampleController::class, 'updateStatus']);
    Route::post('/samples/{id}/collect', [App\Http\Controllers\Api\SampleController::class, 'recordCollection']);
    
    // Results
    Route::get('/results', [App\Http\Controllers\Api\ResultController::class, 'index']);
    Route::get('/results/pending', [App\Http\Controllers\Api\ResultController::class, 'pending']);
    Route::get('/results/{id}', [App\Http\Controllers\Api\ResultController::class, 'show']);
    Route::post('/results', [App\Http\Controllers\Api\ResultController::class, 'store']);
    Route::put('/results/{id}', [App\Http\Controllers\Api\ResultController::class, 'update']);
    Route::put('/results/{id}/verify', [App\Http\Controllers\Api\ResultController::class, 'verify']);
    Route::put('/results/{id}/approve', [App\Http\Controllers\Api\ResultController::class, 'approve']);
});

/*
|--------------------------------------------------------------------------
| Inventory Routes (Admin + Inventory Manager)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:Admin,Inventory'])->group(function () {
    Route::get('/inventory', [App\Http\Controllers\Api\InventoryController::class, 'index']);
    Route::get('/inventory/needs-restock', [App\Http\Controllers\Api\InventoryController::class, 'needsRestock']);
    Route::get('/inventory/low-stock', [App\Http\Controllers\Api\InventoryController::class, 'lowStock']);
    Route::get('/inventory/expired', [App\Http\Controllers\Api\InventoryController::class, 'expired']);
    Route::get('/inventory/expiring-soon', [App\Http\Controllers\Api\InventoryController::class, 'expiringSoon']);
    Route::get('/inventory/category/{category}', [App\Http\Controllers\Api\InventoryController::class, 'byCategory']);
    Route::get('/inventory/{id}', [App\Http\Controllers\Api\InventoryController::class, 'show']);
    Route::post('/inventory', [App\Http\Controllers\Api\InventoryController::class, 'store']);
    Route::put('/inventory/{id}', [App\Http\Controllers\Api\InventoryController::class, 'update']);
    Route::post('/inventory/{id}/restock', [App\Http\Controllers\Api\InventoryController::class, 'restock']);
    Route::post('/inventory/{id}/consume', [App\Http\Controllers\Api\InventoryController::class, 'consume']);
    Route::delete('/inventory/{id}', [App\Http\Controllers\Api\InventoryController::class, 'destroy']);
    Route::get('/inventory/statistics/dashboard', [App\Http\Controllers\Api\InventoryController::class, 'statistics']);
});

/*
|--------------------------------------------------------------------------
| Patient Portal Routes (Authentication + Portal)
|--------------------------------------------------------------------------
*/

// Public Patient Routes
Route::post('/patient/register', [App\Http\Controllers\Api\PatientAuthController::class, 'register']);
Route::post('/patient/login', [App\Http\Controllers\Api\PatientAuthController::class, 'login']);

// Protected Patient Routes
Route::middleware(['auth:sanctum'])->prefix('patient')->group(function () {
    Route::post('/logout', [App\Http\Controllers\Api\PatientAuthController::class, 'logout']);
    
    // Profile
    Route::get('/profile', [App\Http\Controllers\Api\PatientPortalController::class, 'profile']);
    Route::put('/profile', [App\Http\Controllers\Api\PatientPortalController::class, 'updateProfile']);
    
    // Orders
    Route::get('/orders', [App\Http\Controllers\Api\PatientPortalController::class, 'myOrders']);
    Route::get('/orders/{id}', [App\Http\Controllers\Api\PatientPortalController::class, 'showOrder']);
    Route::get('/orders/{id}/track', [App\Http\Controllers\Api\PatientPortalController::class, 'trackOrder']);
    
    // Reports
    Route::get('/reports', [App\Http\Controllers\Api\PatientPortalController::class, 'myReports']);
    Route::get('/reports/{id}', [App\Http\Controllers\Api\PatientPortalController::class, 'showReport']);
    Route::get('/reports/{id}/download', [App\Http\Controllers\Api\PatientPortalController::class, 'downloadReport']);
    
    // Payments
    Route::get('/payments', [App\Http\Controllers\Api\PatientPortalController::class, 'myPayments']);
});