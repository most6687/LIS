<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Models\Sample;
use App\Models\Test;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Results",
 *     description="API endpoints for managing laboratory test results"
 * )
 */
class ResultController extends Controller
{
    /**
     * Get all results
     *
     * @OA\Get(
     *     path="/api/results",
     *     operationId="getResults",
     *     tags={"Results"},
     *     summary="Retrieve all test results",
     *     description="Get a list of all laboratory test results in the system",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="Result_ID", type="integer", example=1),
     *                 @OA\Property(property="Sample_ID", type="integer", example=1),
     *                 @OA\Property(property="User_ID", type="integer", example=1),
     *                 @OA\Property(property="Result_Value", type="string", example="120"),
     *                 @OA\Property(property="Unit", type="string", example="mg/dL"),
     *                 @OA\Property(property="Normal_Range", type="string", example="70-100"),
     *                 @OA\Property(property="Test_Name", type="string", example="Blood Glucose"),
     *                 @OA\Property(property="Method_Used", type="string", example="Enzymatic"),
     *                 @OA\Property(property="Interpretation", type="string", example="High"),
     *                 @OA\Property(property="Status", type="string", enum={"Pending", "Verified", "Approved"}, example="Pending"),
     *                 @OA\Property(property="Result_Date", type="string", format="date-time"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function index()
    {
        $results = Result::with(['sample.test.patient', 'user'])->get();
        return response()->json($results);
    }

    /**
     * Get pending results
     *
     * @OA\Get(
     *     path="/api/results/pending",
     *     operationId="getPendingResults",
     *     tags={"Results"},
     *     summary="Retrieve pending results",
     *     description="Get a list of all pending results that need verification or approval",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="Result_ID", type="integer", example=1),
     *                 @OA\Property(property="Sample_ID", type="integer", example=1),
     *                 @OA\Property(property="Result_Value", type="string"),
     *                 @OA\Property(property="Unit", type="string"),
     *                 @OA\Property(property="Normal_Range", type="string"),
     *                 @OA\Property(property="Test_Name", type="string"),
     *                 @OA\Property(property="Status", type="string", example="Pending"),
     *                 @OA\Property(property="Result_Date", type="string", format="date-time"),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function pending()
    {
        $results = Result::where('Status', 'Pending')
            ->with(['sample.test.patient', 'user'])
            ->get();
        return response()->json($results);
    }

    /**
     * Get a specific result
     *
     * @OA\Get(
     *     path="/api/results/{id}",
     *     operationId="getResult",
     *     tags={"Results"},
     *     summary="Retrieve a result by ID",
     *     description="Get details of a specific laboratory test result",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Result ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="Result_ID", type="integer", example=1),
     *             @OA\Property(property="Sample_ID", type="integer", example=1),
     *             @OA\Property(property="User_ID", type="integer", example=1),
     *             @OA\Property(property="Result_Value", type="string"),
     *             @OA\Property(property="Unit", type="string"),
     *             @OA\Property(property="Normal_Range", type="string"),
     *             @OA\Property(property="Test_Name", type="string"),
     *             @OA\Property(property="Method_Used", type="string"),
     *             @OA\Property(property="Interpretation", type="string"),
     *             @OA\Property(property="Status", type="string", enum={"Pending", "Verified", "Approved"}),
     *             @OA\Property(property="Result_Date", type="string", format="date-time"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Result not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function show($id)
    {
        $result = Result::with(['sample.test.patient', 'user'])->findOrFail($id);
        return response()->json($result);
    }

    /**
     * Create a new result
     *
     * @OA\Post(
     *     path="/api/results",
     *     operationId="storeResult",
     *     tags={"Results"},
     *     summary="Create a new test result",
     *     description="Store a newly created laboratory test result",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Result data",
     *         @OA\JsonContent(
     *             required={"Sample_ID", "Result_Value", "Normal_Range", "Test_Name"},
     *             @OA\Property(property="Sample_ID", type="integer", example=1),
     *             @OA\Property(property="Result_Value", type="string", maxLength=50, example="120"),
     *             @OA\Property(property="Unit", type="string", maxLength=30, nullable=true, example="mg/dL"),
     *             @OA\Property(property="Normal_Range", type="string", maxLength=50, example="70-100"),
     *             @OA\Property(property="Test_Name", type="string", maxLength=100, example="Blood Glucose"),
     *             @OA\Property(property="Method_Used", type="string", maxLength=100, nullable=true, example="Enzymatic"),
     *             @OA\Property(property="Interpretation", type="string", maxLength=200, nullable=true, example="High")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Result created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Result saved"),
     *             @OA\Property(property="result", type="object",
     *                 @OA\Property(property="Result_ID", type="integer"),
     *                 @OA\Property(property="Sample_ID", type="integer"),
     *                 @OA\Property(property="User_ID", type="integer"),
     *                 @OA\Property(property="Result_Value", type="string"),
     *                 @OA\Property(property="Unit", type="string"),
     *                 @OA\Property(property="Normal_Range", type="string"),
     *                 @OA\Property(property="Test_Name", type="string"),
     *                 @OA\Property(property="Method_Used", type="string"),
     *                 @OA\Property(property="Interpretation", type="string"),
     *                 @OA\Property(property="Status", type="string", example="Pending"),
     *                 @OA\Property(property="Result_Date", type="string", format="date-time"),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'Sample_ID' => 'required|exists:samples,Sample_ID',
            'Result_Value' => 'required|string',
            'Unit' => 'nullable|string',
            'Normal_Range' => 'required|string',
            'Test_Name' => 'required|string',
            'Method_Used' => 'nullable|string',
            'Interpretation' => 'nullable|string',
        ]);

        $result = Result::create([
            'User_ID' => Auth::user()->User_ID,
            'Sample_ID' => $request->Sample_ID,
            'Result_Value' => $request->Result_Value,
            'Unit' => $request->Unit,
            'Normal_Range' => $request->Normal_Range,
            'Test_Name' => $request->Test_Name,
            'Method_Used' => $request->Method_Used,
            'Interpretation' => $request->Interpretation,
            'Status' => 'Pending',
            'Result_Date' => now(),
        ]);

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Module' => 'Results',
            'Action' => 'Result Entered',
            'Description' => 'Result entered for Sample #' . $request->Sample_ID
        ]);

        return response()->json(['message' => 'Result saved', 'result' => $result], 201);
    }

    /**
     * Update a result
     *
     * @OA\Put(
     *     path="/api/results/{id}",
     *     operationId="updateResult",
     *     tags={"Results"},
     *     summary="Update a test result",
     *     description="Update an existing laboratory test result",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Result ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Updated result data",
     *         @OA\JsonContent(
     *             @OA\Property(property="Result_Value", type="string", example="120"),
     *             @OA\Property(property="Unit", type="string", nullable=true, example="mg/dL"),
     *             @OA\Property(property="Normal_Range", type="string", example="70-100"),
     *             @OA\Property(property="Interpretation", type="string", nullable=true, example="High")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Result updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Result updated"),
     *             @OA\Property(property="result", type="object",
     *                 @OA\Property(property="Result_ID", type="integer"),
     *                 @OA\Property(property="Sample_ID", type="integer"),
     *                 @OA\Property(property="Result_Value", type="string"),
     *                 @OA\Property(property="Unit", type="string"),
     *                 @OA\Property(property="Normal_Range", type="string"),
     *                 @OA\Property(property="Interpretation", type="string"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Result not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $result = Result::findOrFail($id);

        $request->validate([
            'Result_Value' => 'sometimes|string',
            'Unit' => 'nullable|string',
            'Normal_Range' => 'sometimes|string',
            'Interpretation' => 'nullable|string',
        ]);

        $result->update($request->only(['Result_Value', 'Unit', 'Normal_Range', 'Interpretation']));

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Module' => 'Results',
            'Action' => 'Result Updated',
            'Description' => 'Result updated for Sample #' . $result->Sample_ID
        ]);

        return response()->json(['message' => 'Result updated', 'result' => $result]);
    }

    /**
     * Verify a result
     *
     * @OA\Put(
     *     path="/api/results/{id}/verify",
     *     operationId="verifyResult",
     *     tags={"Results"},
     *     summary="Verify a test result",
     *     description="Mark a test result as verified",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Result ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Result verified successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Result verified")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Result not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function verify($id)
    {
        $result = Result::findOrFail($id);
        $result->update(['Status' => 'Verified']);

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Module' => 'Results',
            'Action' => 'Result Verified',
            'Description' => 'Result verified for Sample #' . $result->Sample_ID
        ]);

        return response()->json(['message' => 'Result verified']);
    }

    /**
     * Approve a result
     *
     * @OA\Put(
     *     path="/api/results/{id}/approve",
     *     operationId="approveResult",
     *     tags={"Results"},
     *     summary="Approve a test result",
     *     description="Mark a test result as approved and complete the sample and test",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Result ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Result approved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Result approved")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Result not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function approve($id)
    {
        $result = Result::findOrFail($id);
        $result->update(['Status' => 'Approved']);

        // تحديث حالة الـ Sample و Test
        $sample = $result->sample;
        if ($sample) {
            $sample->update(['Status' => 'Completed']);
            if ($sample->test) {
                $sample->test->update(['Status' => 'Completed']);
            }
        }

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Module' => 'Results',
            'Action' => 'Result Approved',
            'Description' => 'Result approved for Sample #' . $result->Sample_ID
        ]);

        return response()->json(['message' => 'Result approved']);
    }
}