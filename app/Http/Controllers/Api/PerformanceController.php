<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Patient;
use App\Models\Report;
use App\Models\Test;

class PerformanceController extends Controller
{
    public function index()
    {
        $users = User::count();
        $patients = Patient::count();
        $reports = Report::count();
        $orders = Test::count();

        return response()->json([
            'total_users' => $users,
            'total_patients' => $patients,
            'total_reports' => $reports,
            'total_orders' => $orders,
            'server_status' => 'running'
        ]);
    }
}
