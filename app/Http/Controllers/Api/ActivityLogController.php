<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{

    // عرض كل الـ Logs
    public function index(Request $request)
{
    $logs = DB::table('activity_logs')
        ->join('users', 'users.User_ID', '=', 'activity_logs.User_ID')
        ->select(
            'activity_logs.*',
            'users.Full_Name'
        );

    // filter by user
    if ($request->User_ID) {
        $logs->where('activity_logs.User_ID', $request->User_ID);
    }

    // filter by module
    if ($request->Module) {
        $logs->where('activity_logs.Module', $request->Module);
    }

    // filter by date
    if ($request->Date) {
        $logs->whereDate('activity_logs.created_at', $request->Date);
    }

    $logs = $logs->orderBy('activity_logs.created_at', 'desc')
        ->limit(10)
        ->get();

    return response()->json($logs);
}

}