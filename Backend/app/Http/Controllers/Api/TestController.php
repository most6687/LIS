<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Test;
use App\Models\Patient;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Orders",
 *     description="Manage test orders"
 * )
 */
class TestController extends Controller
{
    /**
     * List all test orders
     *
     * @OA\Get(
     *     path="/api/orders",
     *     operationId="listOrders",
     *     tags={"Orders"},
     *     summary="Get all test orders",
     *     description="Retrieve all test orders with related patient, doctor, user, sample, payment and report information",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of all test orders",
     *         @OA\JsonContent(
     *             type="array",
     *             items=@OA\Items(
     *                 @OA\Property(property="Order_ID", type="integer", example=1),
     *                 @OA\Property(property="Patient_ID", type="integer", example=1),
     *                 @OA\Property(property="Doctor_ID", type="integer", nullable=true, example=5),
     *                 @OA\Property(property="User_ID", type="integer", example=2),
     *                 @OA\Property(property="Order_Date", type="string", format="date-time", example="2026-02-15T10:30:00Z"),
     *                 @OA\Property(property="Priority", type="string", enum={"Urgent","Routine"}, example="Routine"),
     *                 @OA\Property(property="Status", type="string", enum={"Pending","Collected","Processing","Completed"}, example="Pending"),
     *                 @OA\Property(property="Total_Amount", type="number", format="double", example=500.00),
     *                 @OA\Property(property="Requested_Tests", type="string", nullable=true, example="Blood Test, Urinalysis"),
     *                 @OA\Property(property="Notes", type="string", nullable=true, example="Patient fasting"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
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
        $tests = Test::with(['patient', 'doctor', 'user', 'sample', 'payment', 'report'])->get();
        return response()->json($tests);
    }

    /**
     * Get test orders by status
     *
     * @OA\Get(
     *     path="/api/orders/status/{status}",
     *     operationId="getOrdersByStatus",
     *     tags={"Orders"},
     *     summary="Get orders by status",
     *     description="Retrieve test orders filtered by status (Pending, Collected, Processing, Completed)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="path",
     *         required=true,
     *         description="Order status filter",
     *         @OA\Schema(
     *             type="string",
     *             enum={"Pending","Collected","Processing","Completed"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Orders matching the specified status",
     *         @OA\JsonContent(
     *             type="array",
     *             items=@OA\Items(
     *                 @OA\Property(property="Order_ID", type="integer"),
     *                 @OA\Property(property="Patient_ID", type="integer"),
     *                 @OA\Property(property="Status", type="string"),
     *                 @OA\Property(property="Priority", type="string"),
     *                 @OA\Property(property="Total_Amount", type="number")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid status provided"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function byStatus($status)
    {
        $statuses = ['Pending', 'Collected', 'Processing', 'Completed'];
        if (!in_array($status, $statuses)) {
            return response()->json(['message' => 'Invalid status'], 400);
        }

        $tests = Test::where('Status', $status)
            ->with(['patient', 'doctor', 'user'])
            ->get();
        return response()->json($tests);
    }

    /**
     * Get urgent test orders
     *
     * @OA\Get(
     *     path="/api/orders/urgent",
     *     operationId="getUrgentOrders",
     *     tags={"Orders"},
     *     summary="Get urgent test orders",
     *     description="Retrieve all test orders marked as urgent priority",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of urgent test orders",
     *         @OA\JsonContent(type="array", items=@OA\Items())
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function urgent()
    {
        $tests = Test::where('Priority', 'Urgent')
            ->with(['patient', 'doctor', 'user'])
            ->get();
        return response()->json($tests);
    }

    /**
     * Get test orders by patient
     *
     * @OA\Get(
     *     path="/api/orders/patient/{patientId}",
     *     operationId="getOrdersByPatient",
     *     tags={"Orders"},
     *     summary="Get orders for a specific patient",
     *     description="Retrieve all test orders for a specific patient",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="patientId",
     *         in="path",
     *         required=true,
     *         description="Patient ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Orders for the specified patient",
     *         @OA\JsonContent(type="array", items=@OA\Items())
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Patient not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function byPatient($patientId)
    {
        $patient = Patient::find($patientId);
        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }

        $tests = Test::where('Patient_ID', $patientId)
            ->with(['doctor', 'user', 'sample', 'payment', 'report'])
            ->get();
        return response()->json($tests);
    }

    /**
     * Get a single test order
     *
     * @OA\Get(
     *     path="/api/orders/{id}",
     *     operationId="getOrder",
     *     tags={"Orders"},
     *     summary="Get order details",
     *     description="Retrieve detailed information for a specific test order",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order details",
     *         @OA\JsonContent(
     *             @OA\Property(property="Order_ID", type="integer"),
     *             @OA\Property(property="Patient_ID", type="integer"),
     *             @OA\Property(property="Status", type="string"),
     *             @OA\Property(property="Priority", type="string"),
     *             @OA\Property(property="Total_Amount", type="number")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function show($id)
    {
        $test = Test::with(['patient', 'doctor', 'user', 'sample', 'payment', 'report'])->findOrFail($id);
        return response()->json($test);
    }

    /**
     * Create a new test order
     *
     * @OA\Post(
     *     path="/api/orders",
     *     operationId="createOrder",
     *     tags={"Orders"},
     *     summary="Create a new test order",
     *     description="Create a new test order for a patient",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Order data",
     *         @OA\JsonContent(
     *             required={"Patient_ID"},
     *             @OA\Property(property="Patient_ID", type="integer", description="Patient ID", example=1),
     *             @OA\Property(property="Doctor_ID", type="integer", nullable=true, description="Doctor User ID", example=5),
     *             @OA\Property(property="Priority", type="string", enum={"Urgent","Routine"}, description="Order priority", example="Routine"),
     *             @OA\Property(property="Requested_Tests", type="string", nullable=true, description="Tests to perform", example="Blood Test, Urinalysis"),
     *             @OA\Property(property="Notes", type="string", nullable=true, description="Additional notes", example="Patient fasting"),
     *             @OA\Property(property="Total_Amount", type="number", nullable=true, description="Total amount", example=500.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Test order created successfully"),
     *             @OA\Property(property="order", type="object")
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
            'Patient_ID' => 'required|exists:patients,Patient_ID',
            'Doctor_ID' => 'nullable|exists:users,User_ID',
            'Priority' => 'nullable|in:Urgent,Routine',
            'Requested_Tests' => 'nullable|string',
            'Notes' => 'nullable|string',
        ]);

        $test = Test::create([
            'Patient_ID' => $request->Patient_ID,
            'Doctor_ID' => $request->Doctor_ID,
            'User_ID' => Auth::user()->User_ID,
            'Order_Date' => now(),
            'Priority' => $request->Priority ?? 'Routine',
            'Status' => 'Pending',
            'Total_Amount' => $request->Total_Amount ?? 0,
            'Requested_Tests' => $request->Requested_Tests,
            'Notes' => $request->Notes,
        ]);

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Module' => 'Orders',
            'Action' => 'Order Created',
            'Description' => 'Test order created for Patient #' . $request->Patient_ID
        ]);

        return response()->json([
            'message' => 'Test order created successfully',
            'order' => $test
        ], 201);
    }

    /**
     * Update test order status
     *
     * @OA\Put(
     *     path="/api/orders/{id}/status",
     *     operationId="updateOrderStatus",
     *     tags={"Orders"},
     *     summary="Update order status",
     *     description="Update the status of a test order",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Status update data",
     *         @OA\JsonContent(
     *             required={"Status"},
     *             @OA\Property(property="Status", type="string", enum={"Pending","Collected","Processing","Completed"}, example="Collected")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="order", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid status"
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
            'Status' => 'required|in:Pending,Collected,Processing,Completed'
        ]);

        $test = Test::findOrFail($id);
        $oldStatus = $test->Status;
        $test->update(['Status' => $request->Status]);

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Module' => 'Orders',
            'Action' => 'Order Status Updated',
            'Description' => "Order #{$id} status changed from {$oldStatus} to {$request->Status}"
        ]);

        return response()->json([
            'message' => 'Order status updated successfully',
            'order' => $test
        ]);
    }

    /**
     * Update test order priority
     *
     * @OA\Put(
     *     path="/api/orders/{id}/priority",
     *     operationId="updateOrderPriority",
     *     tags={"Orders"},
     *     summary="Update order priority",
     *     description="Update the priority level of a test order",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Priority update data",
     *         @OA\JsonContent(
     *             required={"Priority"},
     *             @OA\Property(property="Priority", type="string", enum={"Urgent","Routine"}, example="Urgent")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Priority updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="order", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid priority"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function updatePriority(Request $request, $id)
    {
        $request->validate([
            'Priority' => 'required|in:Urgent,Routine'
        ]);

        $test = Test::findOrFail($id);
        $test->update(['Priority' => $request->Priority]);

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Module' => 'Orders',
            'Action' => 'Priority Updated',
            'Description' => "Order #{$id} priority changed to {$request->Priority}"
        ]);

        return response()->json([
            'message' => 'Order priority updated successfully',
            'order' => $test
        ]);
    }

    /**
     * Update test order total amount
     *
     * @OA\Put(
     *     path="/api/orders/{id}/amount",
     *     operationId="updateOrderAmount",
     *     tags={"Orders"},
     *     summary="Update order total amount",
     *     description="Update the total amount for a test order",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Amount update data",
     *         @OA\JsonContent(
     *             required={"Total_Amount"},
     *             @OA\Property(property="Total_Amount", type="number", minimum=0, example=750.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Amount updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="order", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid amount"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function updateTotalAmount(Request $request, $id)
    {
        $request->validate([
            'Total_Amount' => 'required|numeric|min:0'
        ]);

        $test = Test::findOrFail($id);
        $test->update(['Total_Amount' => $request->Total_Amount]);

        // تحديث الـ Payment المرتبط
        if ($test->payment) {
            $test->payment->update(['Amount' => $request->Total_Amount]);
        }

        return response()->json([
            'message' => 'Total amount updated successfully',
            'order' => $test
        ]);
    }

    /**
     * Delete a test order
     *
     * @OA\Delete(
     *     path="/api/orders/{id}",
     *     operationId="deleteOrder",
     *     tags={"Orders"},
     *     summary="Delete a test order",
     *     description="Delete (soft delete) a test order",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function destroy($id)
    {
        $test = Test::findOrFail($id);
        $test->delete();

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Module' => 'Orders',
            'Action' => 'Order Deleted',
            'Description' => "Order #{$id} deleted"
        ]);

        return response()->json(['message' => 'Order deleted successfully']);
    }
}