<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\KPIController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PerformanceController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', function (Request $request) {
        return $request->user();
    });
});

Route::middleware(['auth:sanctum','role:Admin'])->group(function () {

    Route::get('/users',[UserController::class,'index']);
    Route::post('/users',[UserController::class,'store']);
    Route::put('/users/{id}',[UserController::class,'update']);
    Route::delete('/users/{id}',[UserController::class,'destroy']);

    Route::get('/roles',[RoleController::class,'index']);
    Route::get('/roles/{id}',[RoleController::class,'show']);
    Route::post('/roles',[RoleController::class,'store']);
    Route::put('/roles/{id}',[RoleController::class,'update']);
    Route::delete('/roles/{id}',[RoleController::class,'destroy']);

    Route::get('/permissions',[PermissionController::class,'index']);
    Route::post('/permissions',[PermissionController::class,'store']);
    Route::get('/permissions/{id}',[PermissionController::class,'show']);
    Route::put('/permissions/{id}',[PermissionController::class,'update']);
    Route::delete('/permissions/{id}',[PermissionController::class,'destroy']);

    Route::post('/assign-permission',[PermissionController::class,'assignPermissionToRole']);

    Route::get('/doctors',[DoctorController::class,'index']);
    Route::post('/doctors',[DoctorController::class,'store']);
    Route::get('/doctors/{id}',[DoctorController::class,'show']);
    Route::put('/doctors/{id}',[DoctorController::class,'update']);
    Route::delete('/doctors/{id}',[DoctorController::class,'destroy']);

    Route::get('/activity-logs',[ActivityLogController::class,'index']);
});

Route::middleware(['auth:sanctum','role:Admin,Receptionist'])->group(function () {

    Route::get('/patients',[PatientController::class,'index']);
    Route::post('/patients',[PatientController::class,'store']);
    Route::get('/patients/{id}',[PatientController::class,'show']);
    Route::put('/patients/{id}',[PatientController::class,'update']);
    Route::delete('/patients/{id}',[PatientController::class,'destroy']);

});

Route::middleware(['auth:sanctum','role:Admin,Receptionist'])->group(function () {

    Route::get('/orders',[App\Http\Controllers\Api\TestController::class,'index']);
    Route::get('/orders/status/{status}',[App\Http\Controllers\Api\TestController::class,'byStatus']);
    Route::get('/orders/urgent',[App\Http\Controllers\Api\TestController::class,'urgent']);
    Route::get('/orders/patient/{patientId}',[App\Http\Controllers\Api\TestController::class,'byPatient']);
    Route::get('/orders/{id}',[App\Http\Controllers\Api\TestController::class,'show']);
    Route::post('/orders',[App\Http\Controllers\Api\TestController::class,'store']);
    Route::put('/orders/{id}/status',[App\Http\Controllers\Api\TestController::class,'updateStatus']);
    Route::put('/orders/{id}/priority',[App\Http\Controllers\Api\TestController::class,'updatePriority']);
    Route::put('/orders/{id}/amount',[App\Http\Controllers\Api\TestController::class,'updateTotalAmount']);
    Route::delete('/orders/{id}',[App\Http\Controllers\Api\TestController::class,'destroy']);

});

Route::middleware(['auth:sanctum','role:Admin,Receptionist,Technician'])->group(function () {

    Route::get('/reports',[App\Http\Controllers\Api\ReportController::class,'index']);
    Route::get('/reports/status/{status}',[App\Http\Controllers\Api\ReportController::class,'byStatus']);
    Route::get('/reports/type/{type}',[App\Http\Controllers\Api\ReportController::class,'byType']);
    Route::get('/reports/patient/{patientId}',[App\Http\Controllers\Api\ReportController::class,'byPatient']);
    Route::get('/reports/{id}',[App\Http\Controllers\Api\ReportController::class,'show']);
    Route::post('/reports',[App\Http\Controllers\Api\ReportController::class,'store']);
    Route::put('/reports/{id}',[App\Http\Controllers\Api\ReportController::class,'update']);
    Route::put('/reports/{id}/finalize',[App\Http\Controllers\Api\ReportController::class,'finalize']);
    Route::post('/reports/{id}/upload',[App\Http\Controllers\Api\ReportController::class,'uploadFile']);
    Route::get('/reports/{id}/download',[App\Http\Controllers\Api\ReportController::class,'download']);
    Route::delete('/reports/{id}',[App\Http\Controllers\Api\ReportController::class,'destroy']);

});

Route::middleware(['auth:sanctum','role:Admin,Billing'])->group(function () {

    Route::get('/invoices',[InvoiceController::class,'index']);
    Route::post('/invoices',[InvoiceController::class,'store']);
    Route::get('/invoices/{id}',[InvoiceController::class,'show']);
    Route::put('/invoices/{id}',[InvoiceController::class,'update']);
    Route::delete('/invoices/{id}',[InvoiceController::class,'destroy']);

    Route::get('/invoices/patient/{patientId}',[InvoiceController::class,'byPatient']);
    Route::get('/invoices/status/{status}',[InvoiceController::class,'byStatus']);
    Route::put('/invoices/{id}/mark-paid',[InvoiceController::class,'markPaid']);

    Route::get('/payments',[App\Http\Controllers\Api\BillingController::class,'index']);
    Route::get('/payments/status/{status}',[App\Http\Controllers\Api\BillingController::class,'byStatus']);
    Route::get('/payments/method/{method}',[App\Http\Controllers\Api\BillingController::class,'byMethod']);
    Route::get('/payments/overdue',[App\Http\Controllers\Api\BillingController::class,'overdue']);
    Route::get('/payments/patient/{patientId}',[App\Http\Controllers\Api\BillingController::class,'byPatient']);
    Route::get('/payments/order/{orderId}',[App\Http\Controllers\Api\BillingController::class,'byOrder']);
    Route::get('/payments/{id}',[App\Http\Controllers\Api\BillingController::class,'show']);
    Route::post('/payments',[App\Http\Controllers\Api\BillingController::class,'store']);
    Route::put('/payments/{id}',[App\Http\Controllers\Api\BillingController::class,'update']);
    Route::post('/payments/{id}/add',[App\Http\Controllers\Api\BillingController::class,'addPayment']);
    Route::put('/payments/{id}/status',[App\Http\Controllers\Api\BillingController::class,'updateStatus']);
    Route::delete('/payments/{id}',[App\Http\Controllers\Api\BillingController::class,'destroy']);
    Route::get('/payments/statistics/dashboard',[App\Http\Controllers\Api\BillingController::class,'statistics']);

});

Route::middleware(['auth:sanctum','role:Admin'])->group(function () {

    Route::get('/dashboard',[DashboardController::class,'index']);
    Route::get('/kpis',[KPIController::class,'index']);

    Route::get('/notifications',[NotificationController::class,'index']);
    Route::post('/notifications',[NotificationController::class,'store']);
    Route::put('/notifications/{id}/read',[NotificationController::class,'markAsRead']);
    Route::delete('/notifications/{id}',[NotificationController::class,'destroy']);

    Route::get('/performance',[PerformanceController::class,'index']);

});
