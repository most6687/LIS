<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Test;
use App\Models\Patient;
use App\Models\Invoice;

class KPIController extends Controller
{
    public function index()
    {

        $totalPatients = Patient::count();

        $totalTests = Test::count();

        $totalReports = Report::count();

        $draftReports = Report::where('Report_Status','Draft')->count();

        $finalReports = Report::where('Report_Status','Final')->count();

        $revenue = Invoice::where('status','paid')->sum('paid_amount');

        return response()->json([
            'patients' => $totalPatients,
            'tests' => $totalTests,
            'reports' => $totalReports,
            'draft_reports' => $draftReports,
            'final_reports' => $finalReports,
            'revenue' => $revenue
        ]);

    }
}
