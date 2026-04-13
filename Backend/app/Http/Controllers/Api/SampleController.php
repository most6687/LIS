<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sample;
use App\Models\Test;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Samples",
 *     description="API endpoints for managing laboratory samples"
 * )
 */
class SampleController extends Controller
{
    /**
     * Get all samples
     *
     * @OA\Get(
     *     path="/api/samples",
     *     operationId="getSamples",
     *     tags={"Samples"},
     *     summary="Retrieve all samples",
     *     description="Get a list of all laboratory samples in the system",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="Sample_ID", type="integer", example=1),
     *                 @OA\Property(property="Order_ID", type="integer", example=1),
     *                 @OA\Property(property="User_ID", type="integer", example=1),
     *                 @OA\Property(property="Status", type="string", enum={"Pending", "Collected", "In_Analysis", "Completed"}, example="Pending"),
     *                 @OA\Property(property="Collection_Date", type="string", format="date-time"),
     *                 @OA\Property(property="Storage_Location", type="string", example="Freezer A1"),
     *                 @OA\Property(property="Container_Type", type="string", example="Tube"),
     *                 @OA\Property(property="Volume", type="string", example="5ml"),
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
        $samples = Sample::with(['test.patient'])->get();
        return response()->json($samples);
    }

    /**
     * Get pending samples
     *
     * @OA\Get(
     *     path="/api/samples/pending",
     *     operationId="getPendingSamples",
     *     tags={"Samples"},
     *     summary="Retrieve pending samples",
     *     description="Get a list of all pending samples that need to be collected",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="Sample_ID", type="integer", example=1),
     *                 @OA\Property(property="Order_ID", type="integer", example=1),
     *                 @OA\Property(property="Status", type="string", example="Pending"),
     *                 @OA\Property(property="Collection_Date", type="string", format="date-time"),
     *                 @OA\Property(property="Storage_Location", type="string"),
     *                 @OA\Property(property="Container_Type", type="string"),
     *                 @OA\Property(property="Volume", type="string"),
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
        $samples = Sample::pending()->with(['test.patient'])->get();
        return response()->json($samples);
    }

    /**
     * Get a specific sample
     *
     * @OA\Get(
     *     path="/api/samples/{id}",
     *     operationId="getSample",
     *     tags={"Samples"},
     *     summary="Retrieve a sample by ID",
     *     description="Get details of a specific laboratory sample",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Sample ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="Sample_ID", type="integer", example=1),
     *             @OA\Property(property="Order_ID", type="integer", example=1),
     *             @OA\Property(property="User_ID", type="integer", example=1),
     *             @OA\Property(property="Status", type="string", enum={"Pending", "Collected", "In_Analysis", "Completed"}),
     *             @OA\Property(property="Collection_Date", type="string", format="date-time"),
     *             @OA\Property(property="Storage_Location", type="string"),
     *             @OA\Property(property="Container_Type", type="string"),
     *             @OA\Property(property="Volume", type="string"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sample not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function show($id)
    {
        $sample = Sample::with(['test.patient', 'user'])->findOrFail($id);
        return response()->json($sample);
    }

    /**
     * Update sample status
     *
     * @OA\Put(
     *     path="/api/samples/{id}/status",
     *     operationId="updateSampleStatus",
     *     tags={"Samples"},
     *     summary="Update sample status",
     *     description="Update the status of a laboratory sample (Pending, Collected, In_Analysis, Completed)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Sample ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Sample status data",
     *         @OA\JsonContent(
     *             required={"Status"},
     *             @OA\Property(property="Status", type="string", enum={"Pending", "Collected", "In_Analysis", "Completed"}, example="Collected")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sample status updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Sample status updated"),
     *             @OA\Property(property="sample", type="object",
     *                 @OA\Property(property="Sample_ID", type="integer"),
     *                 @OA\Property(property="Status", type="string"),
     *                 @OA\Property(property="Collection_Date", type="string", format="date-time"),
     *                 @OA\Property(property="Storage_Location", type="string"),
     *                 @OA\Property(property="Container_Type", type="string"),
     *                 @OA\Property(property="Volume", type="string"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sample not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'Status' => 'required|in:Pending,Collected,In_Analysis,Completed'
        ]);

        $sample = Sample::findOrFail($id);
        $sample->update(['Status' => $request->Status]);

        // تحديث حالة الـ Test تبعاً للعينة
        if ($request->Status === 'Completed') {
            $sample->test()->update(['Status' => 'Completed']);
        }

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Module' => 'Samples',
            'Action' => 'Update Status',
            'Description' => 'Sample status updated to: ' . $request->Status
        ]);

        return response()->json(['message' => 'Sample status updated', 'sample' => $sample]);
    }

    /**
     * Record sample collection
     *
     * @OA\Post(
     *     path="/api/samples/{id}/collect",
     *     operationId="recordSampleCollection",
     *     tags={"Samples"},
     *     summary="Record sample collection",
     *     description="Record the collection of a laboratory sample with storage details",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Sample ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         description="Collection details",
     *         @OA\JsonContent(
     *             @OA\Property(property="Storage_Location", type="string", maxLength=50, nullable=true, example="Freezer A1"),
     *             @OA\Property(property="Container_Type", type="string", maxLength=50, nullable=true, example="Tube"),
     *             @OA\Property(property="Volume", type="string", maxLength=20, nullable=true, example="5ml")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sample collection recorded successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Sample collection recorded"),
     *             @OA\Property(property="sample", type="object",
     *                 @OA\Property(property="Sample_ID", type="integer"),
     *                 @OA\Property(property="Status", type="string", example="Collected"),
     *                 @OA\Property(property="Collection_Date", type="string", format="date-time"),
     *                 @OA\Property(property="Storage_Location", type="string"),
     *                 @OA\Property(property="Container_Type", type="string"),
     *                 @OA\Property(property="Volume", type="string"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sample not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function recordCollection(Request $request, $id)
    {
        $request->validate([
            'Storage_Location' => 'nullable|string',
            'Container_Type' => 'nullable|string',
            'Volume' => 'nullable|string',
        ]);

        $sample = Sample::findOrFail($id);
        $sample->update([
            'Status' => 'Collected',
            'Collection_Date' => now(),
            'Storage_Location' => $request->Storage_Location,
            'Container_Type' => $request->Container_Type,
            'Volume' => $request->Volume,
        ]);

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Module' => 'Samples',
            'Action' => 'Collection Recorded',
            'Description' => 'Sample collected for Order #' . $sample->Order_ID
        ]);

        return response()->json(['message' => 'Sample collection recorded', 'sample' => $sample]);
    }
}