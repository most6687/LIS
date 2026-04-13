<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Test;
use App\Models\Patient;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Billing",
 *     description="Manage laboratory billing and payments"
 * )
 */
class BillingController extends Controller
{
    /**
     * List all payments
     *
     * @OA\Get(
     *     path="/api/billing",
     *     operationId="listPayments",
     *     tags={"Billing"},
     *     summary="Get all payments",
     *     description="Retrieve all payment records with related patient, user and test information",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of all payments",
     *         @OA\JsonContent(
     *             type="array",
     *             items=@OA\Items(
     *                 @OA\Property(property="Payment_ID", type="integer", example=1),
     *                 @OA\Property(property="Order_ID", type="integer", example=1),
     *                 @OA\Property(property="Patient_ID", type="integer", example=1),
     *                 @OA\Property(property="User_ID", type="integer", example=2),
     *                 @OA\Property(property="Payment_Date", type="string", format="date-time", example="2026-02-15T14:30:00Z"),
     *                 @OA\Property(property="Amount", type="number", format="float", example=150.00),
     *                 @OA\Property(property="Payment_Method", type="string", enum={"Cash","Card","Electronic"}, example="Cash"),
     *                 @OA\Property(property="Payment_Status", type="string", enum={"Paid","Unpaid","Partial"}, example="Paid"),
     *                 @OA\Property(property="Transaction_ID", type="string", nullable=true, example="TXN123456"),
     *                 @OA\Property(property="Invoice_Number", type="string", example="INV-000001"),
     *                 @OA\Property(property="Billing_Date", type="string", format="date", example="2026-02-15"),
     *                 @OA\Property(property="Due_Date", type="string", format="date", nullable=true, example="2026-02-20"),
     *                 @OA\Property(property="Notes", type="string", nullable=true, example="Full payment received"),
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
        $payments = Payment::with(['patient', 'user', 'test'])->get();
        return response()->json($payments);
    }

    /**
     * Get payments by status
     *
     * @OA\Get(
     *     path="/api/billing/status/{status}",
     *     operationId="getPaymentsByStatus",
     *     tags={"Billing"},
     *     summary="Get payments by status",
     *     description="Retrieve payments filtered by status (Paid, Unpaid, or Partial)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="path",
     *         required=true,
     *         description="Payment status filter",
     *         @OA\Schema(
     *             type="string",
     *             enum={"Paid","Unpaid","Partial"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payments matching the specified status",
     *         @OA\JsonContent(type="array", items=@OA\Items())
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid status"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function byStatus($status)
    {
        $statuses = ['Paid', 'Unpaid', 'Partial'];
        if (!in_array($status, $statuses)) {
            return response()->json(['message' => 'Invalid status'], 400);
        }

        $payments = Payment::where('Payment_Status', $status)
            ->with(['patient', 'user', 'test'])
            ->get();
        return response()->json($payments);
    }

    /**
     * Get payments by method
     *
     * @OA\Get(
     *     path="/api/billing/method/{method}",
     *     operationId="getPaymentsByMethod",
     *     tags={"Billing"},
     *     summary="Get payments by method",
     *     description="Retrieve payments filtered by payment method (Cash, Card, or Electronic)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="method",
     *         in="path",
     *         required=true,
     *         description="Payment method filter",
     *         @OA\Schema(
     *             type="string",
     *             enum={"Cash","Card","Electronic"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payments matching the specified method",
     *         @OA\JsonContent(type="array", items=@OA\Items())
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid method"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function byMethod($method)
    {
        $methods = ['Cash', 'Card', 'Electronic'];
        if (!in_array($method, $methods)) {
            return response()->json(['message' => 'Invalid method'], 400);
        }

        $payments = Payment::where('Payment_Method', $method)
            ->with(['patient', 'user', 'test'])
            ->get();
        return response()->json($payments);
    }

    /**
     * Get overdue payments
     *
     * @OA\Get(
     *     path="/api/billing/overdue",
     *     operationId="getOverduePayments",
     *     tags={"Billing"},
     *     summary="Get overdue payments",
     *     description="Retrieve payments that are past due date and not fully paid",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of overdue payments",
     *         @OA\JsonContent(type="array", items=@OA\Items())
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function overdue()
    {
        $payments = Payment::where('Due_Date', '<', now())
            ->where('Payment_Status', '!=', 'Paid')
            ->with(['patient', 'user', 'test'])
            ->get();
        return response()->json($payments);
    }

    /**
     * Get payments for a specific patient
     *
     * @OA\Get(
     *     path="/api/billing/patient/{patientId}",
     *     operationId="getPaymentsByPatient",
     *     tags={"Billing"},
     *     summary="Get patient payments",
     *     description="Retrieve all payments for a specific patient",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="patientId",
         in="path",
     *         required=true,
     *         description="Patient ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payments for the specified patient",
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

        $payments = Payment::where('Patient_ID', $patientId)
            ->with(['user', 'test'])
            ->get();
        return response()->json($payments);
    }

    /**
     * Get payment for a specific order
     *
     * @OA\Get(
     *     path="/api/billing/order/{orderId}",
     *     operationId="getPaymentByOrder",
     *     tags={"Billing"},
     *     summary="Get order payment",
     *     description="Retrieve payment information for a specific test order",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         required=true,
     *         description="Test Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment for the specified order",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order or payment not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function byOrder($orderId)
    {
        $test = Test::find($orderId);
        if (!$test) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $payment = Payment::where('Order_ID', $orderId)
            ->with(['patient', 'user'])
            ->first();

        if (!$payment) {
            return response()->json(['message' => 'No payment found for this order'], 404);
        }

        return response()->json($payment);
    }

    /**
     * Get a single payment
     *
     * @OA\Get(
     *     path="/api/billing/{id}",
     *     operationId="getPayment",
     *     tags={"Billing"},
     *     summary="Get payment details",
     *     description="Retrieve detailed information for a specific payment",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Payment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment details",
     *         @OA\JsonContent(
     *             @OA\Property(property="Payment_ID", type="integer"),
     *             @OA\Property(property="Order_ID", type="integer"),
     *             @OA\Property(property="Patient_ID", type="integer"),
     *             @OA\Property(property="Amount", type="number"),
     *             @OA\Property(property="Payment_Status", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function show($id)
    {
        $payment = Payment::with(['patient', 'user', 'test'])->findOrFail($id);
        return response()->json($payment);
    }

    /**
     * Create a new payment
     *
     * @OA\Post(
     *     path="/api/billing",
     *     operationId="createPayment",
     *     tags={"Billing"},
     *     summary="Create a new payment",
     *     description="Create a new payment record for a test order",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Payment data",
     *         @OA\JsonContent(
     *             required={"Order_ID","Amount"},
     *             @OA\Property(property="Order_ID", type="integer", description="Test Order ID", example=1),
     *             @OA\Property(property="Amount", type="number", format="float", description="Payment amount", example=150.00),
     *             @OA\Property(property="Payment_Method", type="string", enum={"Cash","Card","Electronic"}, description="Payment method", example="Cash"),
     *             @OA\Property(property="Transaction_ID", type="string", nullable=true, description="Transaction ID", example="TXN123456"),
     *             @OA\Property(property="Invoice_Number", type="string", nullable=true, description="Invoice number", example="INV-000001"),
     *             @OA\Property(property="Billing_Date", type="string", format="date", nullable=true, description="Billing date", example="2026-02-15"),
     *             @OA\Property(property="Due_Date", type="string", format="date", nullable=true, description="Due date", example="2026-02-20"),
     *             @OA\Property(property="Notes", type="string", nullable=true, description="Additional notes", example="Full payment received")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Payment created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment created successfully"),
     *             @OA\Property(property="payment", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Payment already exists for this order"
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
            'Order_ID' => 'required|exists:tests,Order_ID',
            'Amount' => 'required|numeric|min:0',
            'Payment_Method' => 'nullable|in:Cash,Card,Electronic',
            'Transaction_ID' => 'nullable|string|max:50',
            'Invoice_Number' => 'nullable|string|max:50',
            'Billing_Date' => 'nullable|date',
            'Due_Date' => 'nullable|date|after_or_equal:today',
            'Notes' => 'nullable|string',
        ]);

        $test = Test::findOrFail($request->Order_ID);

        // اتأكد إن مفيش دفعة موجودة بالفعل
        $existingPayment = Payment::where('Order_ID', $request->Order_ID)->first();
        if ($existingPayment) {
            return response()->json([
                'message' => 'Payment already exists for this order'
            ], 409);
        }

        // تحديد حالة الدفع
        $paymentStatus = 'Unpaid';
        if ($request->Amount >= $test->Total_Amount) {
            $paymentStatus = 'Paid';
        } elseif ($request->Amount > 0) {
            $paymentStatus = 'Partial';
        }

        $payment = Payment::create([
            'Order_ID' => $request->Order_ID,
            'Patient_ID' => $test->Patient_ID,
            'User_ID' => Auth::user()->User_ID,
            'Payment_Date' => now(),
            'Amount' => $request->Amount,
            'Payment_Method' => $request->Payment_Method ?? 'Cash',
            'Payment_Status' => $paymentStatus,
            'Transaction_ID' => $request->Transaction_ID,
            'Invoice_Number' => $request->Invoice_Number ?? 'INV-' . str_pad($request->Order_ID, 6, '0', STR_PAD_LEFT),
            'Billing_Date' => $request->Billing_Date ?? now(),
            'Due_Date' => $request->Due_Date,
            'Notes' => $request->Notes,
        ]);

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Module' => 'Billing',
            'Action' => 'Payment Created',
            'Description' => 'Payment created for Order #' . $request->Order_ID . ' with amount ' . $request->Amount
        ]);

        return response()->json([
            'message' => 'Payment created successfully',
            'payment' => $payment
        ], 201);
    }

    /**
     * Update a payment
     *
     * @OA\Put(
     *     path="/api/billing/{id}",
     *     operationId="updatePayment",
     *     tags={"Billing"},
     *     summary="Update payment",
     *     description="Update payment information",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Payment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Payment update data",
     *         @OA\JsonContent(
     *             @OA\Property(property="Amount", type="number", format="float", example=200.00),
     *             @OA\Property(property="Payment_Method", type="string", enum={"Cash","Card","Electronic"}, example="Card"),
     *             @OA\Property(property="Transaction_ID", type="string", nullable=true, example="TXN789012"),
     *             @OA\Property(property="Notes", type="string", nullable=true, example="Updated payment details")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="payment", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found"
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
        $payment = Payment::findOrFail($id);

        $request->validate([
            'Amount' => 'sometimes|numeric|min:0',
            'Payment_Method' => 'sometimes|in:Cash,Card,Electronic',
            'Transaction_ID' => 'nullable|string|max:50',
            'Notes' => 'nullable|string',
        ]);

        $payment->update($request->only(['Amount', 'Payment_Method', 'Transaction_ID', 'Notes']));

        // تحديث حالة الدفع بناءً على المبلغ الجديد
        if ($request->has('Amount')) {
            $test = $payment->test;
            if ($payment->Amount >= $test->Total_Amount) {
                $payment->Payment_Status = 'Paid';
            } elseif ($payment->Amount > 0) {
                $payment->Payment_Status = 'Partial';
            } else {
                $payment->Payment_Status = 'Unpaid';
            }
            $payment->save();
        }

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Module' => 'Billing',
            'Action' => 'Payment Updated',
            'Description' => 'Payment #' . $id . ' updated'
        ]);

        return response()->json([
            'message' => 'Payment updated successfully',
            'payment' => $payment
        ]);
    }

    /**
     * Add additional payment
     *
     * @OA\Post(
     *     path="/api/billing/{id}/add-payment",
     *     operationId="addAdditionalPayment",
     *     tags={"Billing"},
     *     summary="Add additional payment",
     *     description="Record an additional payment amount to an existing payment record",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Payment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Additional payment data",
     *         @OA\JsonContent(
     *             required={"additional_amount"},
     *             @OA\Property(property="additional_amount", type="number", format="float", description="Additional payment amount", example=50.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Additional payment recorded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="payment", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or amount exceeds order total"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function addPayment(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        $request->validate([
            'additional_amount' => 'required|numeric|min:0.01'
        ]);

        $test = $payment->test;
        $newTotal = $payment->Amount + $request->additional_amount;

        if ($newTotal > $test->Total_Amount) {
            return response()->json([
                'message' => 'Total payment cannot exceed order amount',
                'order_total' => $test->Total_Amount,
                'current_paid' => $payment->Amount,
                'max_additional' => $test->Total_Amount - $payment->Amount
            ], 422);
        }

        $payment->Amount = $newTotal;
        
        if ($payment->Amount >= $test->Total_Amount) {
            $payment->Payment_Status = 'Paid';
        } else {
            $payment->Payment_Status = 'Partial';
        }
        
        $payment->save();

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Module' => 'Billing',
            'Action' => 'Additional Payment',
            'Description' => 'Additional payment of ' . $request->additional_amount . ' added to Payment #' . $id
        ]);

        return response()->json([
            'message' => 'Additional payment recorded successfully',
            'payment' => $payment
        ]);
    }

    /**
     * Update payment status
     *
     * @OA\Put(
     *     path="/api/billing/{id}/status",
     *     operationId="updatePaymentStatus",
     *     tags={"Billing"},
     *     summary="Update payment status",
     *     description="Manually update the payment status",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Payment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Status update data",
     *         @OA\JsonContent(
     *             required={"Payment_Status"},
     *             @OA\Property(property="Payment_Status", type="string", enum={"Paid","Unpaid","Partial"}, description="New payment status", example="Paid")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="payment", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found"
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
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'Payment_Status' => 'required|in:Paid,Unpaid,Partial'
        ]);

        $payment = Payment::findOrFail($id);
        $payment->update(['Payment_Status' => $request->Payment_Status]);

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Module' => 'Billing',
            'Action' => 'Payment Status Updated',
            'Description' => 'Payment #' . $id . ' status changed to ' . $request->Payment_Status
        ]);

        return response()->json([
            'message' => 'Payment status updated successfully',
            'payment' => $payment
        ]);
    }

    /**
     * Delete a payment
     *
     * @OA\Delete(
     *     path="/api/billing/{id}",
     *     operationId="deletePayment",
     *     tags={"Billing"},
     *     summary="Delete a payment",
     *     description="Delete a payment record",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Payment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment deleted successfully")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Module' => 'Billing',
            'Action' => 'Payment Deleted',
            'Description' => 'Payment #' . $id . ' deleted'
        ]);

        return response()->json(['message' => 'Payment deleted successfully']);
    }

    /**
     * Get billing statistics
     *
     * @OA\Get(
     *     path="/api/billing/statistics",
     *     operationId="getBillingStatistics",
     *     tags={"Billing"},
     *     summary="Get billing statistics",
     *     description="Retrieve billing and payment statistics for dashboard",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Billing statistics",
     *         @OA\JsonContent(
     *             @OA\Property(property="total_revenue", type="number", format="float", example=15000.00),
     *             @OA\Property(property="total_paid", type="number", format="float", example=12000.00),
     *             @OA\Property(property="total_unpaid", type="number", format="float", example=2000.00),
     *             @OA\Property(property="total_partial", type="number", format="float", example=1000.00),
     *             @OA\Property(property="counts", type="object",
     *                 @OA\Property(property="paid", type="integer", example=45),
     *                 @OA\Property(property="unpaid", type="integer", example=8),
     *                 @OA\Property(property="partial", type="integer", example=12)
     *             ),
     *             @OA\Property(property="by_method", type="array", items=@OA\Items(
     *                 @OA\Property(property="Payment_Method", type="string", example="Cash"),
     *                 @OA\Property(property="total", type="number", format="float", example=8500.00)
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function statistics()
    {
        $totalPaid = Payment::where('Payment_Status', 'Paid')->sum('Amount');
        $totalUnpaid = Payment::where('Payment_Status', 'Unpaid')->sum('Amount');
        $totalPartial = Payment::where('Payment_Status', 'Partial')->sum('Amount');
        
        $paidCount = Payment::where('Payment_Status', 'Paid')->count();
        $unpaidCount = Payment::where('Payment_Status', 'Unpaid')->count();
        $partialCount = Payment::where('Payment_Status', 'Partial')->count();

        $paymentsByMethod = Payment::select('Payment_Method', DB::raw('SUM(Amount) as total'))
            ->groupBy('Payment_Method')
            ->get();

        return response()->json([
            'total_revenue' => $totalPaid + $totalPartial,
            'total_paid' => $totalPaid,
            'total_unpaid' => $totalUnpaid,
            'total_partial' => $totalPartial,
            'counts' => [
                'paid' => $paidCount,
                'unpaid' => $unpaidCount,
                'partial' => $partialCount,
            ],
            'by_method' => $paymentsByMethod,
        ]);
    }
}