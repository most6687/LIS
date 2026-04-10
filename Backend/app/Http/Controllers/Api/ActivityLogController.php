<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{

    /**
     * @OA\Get(
     *     path="/activity-logs",
     *     summary="Get activity logs with optional filters",
     *     tags={"Activity Logs"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="User_ID",
     *         in="query",
     *         required=false,
     *         description="Filter by user ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="Module",
     *         in="query",
     *         required=false,
     *         description="Filter by module name",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="Date",
     *         in="query",
     *         required=false,
     *         description="Filter by date (YYYY-MM-DD format)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of activity logs (latest 10)",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="Log_ID", type="integer"),
     *                 @OA\Property(property="User_ID", type="integer"),
     *                 @OA\Property(property="Module", type="string"),
     *                 @OA\Property(property="Action", type="string"),
     *                 @OA\Property(property="Description", type="string"),
     *                 @OA\Property(property="Full_Name", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
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