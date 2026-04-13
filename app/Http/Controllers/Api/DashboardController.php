<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Test;
use App\Models\Invoice;
use App\Models\Sample;
use App\Models\Report;

class DashboardController extends Controller
{

    public function index()
    {

        $totalPatients = Patient::count();
        $totalDoctors = Doctor::count();
        $totalTests = Test::count();
        $totalInvoices = Invoice::count();

        $paidInvoices = Invoice::where('status','paid')->count();
        $unpaidInvoices = Invoice::where('status','unpaid')->count();

        $totalRevenue = Invoice::where('status','paid')->sum('paid_amount');

        $todaySamples = Sample::whereDate('created_at', today())->count();
        $todayReports = Report::whereDate('created_at', today())->count();

        return response()->json([

            "patients" => $totalPatients,
            "doctors" => $totalDoctors,
            "tests" => $totalTests,
            "invoices" => $totalInvoices,

            "paid_invoices" => $paidInvoices,
            "unpaid_invoices" => $unpaidInvoices,

            "revenue" => $totalRevenue,

            "today_samples" => $todaySamples,
            "today_reports" => $todayReports

        ]);

    }

}
