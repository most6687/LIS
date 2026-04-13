<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Test;
use App\Models\Report;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Patient Portal",
 *     description="Patient portal endpoints for managing personal data, orders, reports, and payments"
 * )
 */
class PatientPortalController extends Controller
{
    private function getAuthenticatedPatient()
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        return Patient::where('Email', $user->Email)->first();
    }

    /**
     * @OA\Get(
     *     path="/api/patient/profile",
     *     summary="Get patient profile",
     *     description="Retrieves the authenticated patient's profile information",
     *     operationId="getPatientProfile",
     *     tags={"Patient Portal"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Profile retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", ref="#/components/schemas/User"),
     *             @OA\Property(property="patient", ref="#/components/schemas/Patient")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Patient profile not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Patient profile not found")
     *         )
     *     )
     * )
     */
    public function profile()
    {
        $patient = $this->getAuthenticatedPatient();
        
        if (!$patient) {
            return response()->json(['message' => 'Patient profile not found'], 404);
        }
        
        return response()->json([
            'user' => $user,
            'patient' => $patient
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/patient/profile",
     *     summary="Update patient profile",
     *     description="Updates the authenticated patient's profile information",
     *     operationId="updatePatientProfile",
     *     tags={"Patient Portal"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="Phone", type="string", maxLength=20, nullable=true, example="+1234567890"),
     *             @OA\Property(property="Address", type="string", maxLength=200, nullable=true, example="123 Main St"),
     *             @OA\Property(property="Insurance_Info", type="string", maxLength=100, nullable=true, example="Insurance Company")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Profile updated successfully"),
     *             @OA\Property(property="patient", ref="#/components/schemas/Patient")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Patient profile not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Patient profile not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function updateProfile(Request $request)
    {
        $patient = $this->getAuthenticatedPatient();
        
        if (!$patient) {
            return response()->json(['message' => 'Patient profile not found'], 404);
        }
        
        $request->validate([
            'Phone' => 'nullable|string|max:20',
            'Address' => 'nullable|string|max:200',
            'Insurance_Info' => 'nullable|string|max:100',
        ]);
        
        $patient->update($request->only(['Phone', 'Address', 'Insurance_Info']));
        
        return response()->json([
            'message' => 'Profile updated successfully',
            'patient' => $patient
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/patient/orders",
     *     summary="Get patient's orders",
     *     description="Retrieves all test orders for the authenticated patient",
     *     operationId="getPatientOrders",
     *     tags={"Patient Portal"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Orders retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Test")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Patient not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Patient not found")
     *         )
     *     )
     * )
     */
    public function myOrders()
    {
        $patient = $this->getAuthenticatedPatient();
        
        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }
        
        $orders = Test::where('Patient_ID', $patient->Patient_ID)
            ->with(['doctor', 'sample', 'payment'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($orders);
    }

    /**
     * @OA\Get(
     *     path="/api/patient/orders/{id}",
     *     summary="Get specific order",
     *     description="Retrieves a specific test order for the authenticated patient",
     *     operationId="getPatientOrder",
     *     tags={"Patient Portal"},
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
     *         description="Order retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Test")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order not found")
     *         )
     *     )
     * )
     */
    public function showOrder($id)
    {
        $patient = $this->getAuthenticatedPatient();
        
        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }
        
        $order = Test::where('Patient_ID', $patient->Patient_ID)
            ->where('Order_ID', $id)
            ->with(['doctor', 'sample', 'payment'])
            ->first();
        
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        
        return response()->json($order);
    }

    /**
     * @OA\Get(
     *     path="/api/patient/reports",
     *     summary="Get patient's reports",
     *     description="Retrieves all reports for the authenticated patient",
     *     operationId="getPatientReports",
     *     tags={"Patient Portal"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Reports retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Report")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Patient not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Patient not found")
     *         )
     *     )
     * )
     */
    public function myReports()
    {
        $patient = $this->getAuthenticatedPatient();
        
        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }
        
        $reports = Report::where('Patient_ID', $patient->Patient_ID)
            ->with(['doctor', 'test'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($reports);
    }

    /**
     * @OA\Get(
     *     path="/api/patient/reports/{id}",
     *     summary="Get specific report",
     *     description="Retrieves a specific report for the authenticated patient",
     *     operationId="getPatientReport",
     *     tags={"Patient Portal"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Report ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Report retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Report")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Report not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Report not found")
     *         )
     *     )
     * )
     */
    public function showReport($id)
    {
        $patient = $this->getAuthenticatedPatient();
        
        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }
        
        $report = Report::where('Patient_ID', $patient->Patient_ID)
            ->where('Report_ID', $id)
            ->with(['doctor', 'test'])
            ->first();
        
        if (!$report) {
            return response()->json(['message' => 'Report not found'], 404);
        }
        
        return response()->json($report);
    }

    /**
     * @OA\Get(
     *     path="/api/patient/reports/{id}/download",
     *     summary="Download report file",
     *     description="Downloads the PDF file for a specific report",
     *     operationId="downloadPatientReport",
     *     tags={"Patient Portal"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Report ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File download initiated",
     *         @OA\MediaType(mediaType="application/pdf")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Report file not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Report file not found")
     *         )
     *     )
     * )
     */
    public function downloadReport($id)
    {
        $patient = $this->getAuthenticatedPatient();
        
        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }
        
        $report = Report::where('Patient_ID', $patient->Patient_ID)
            ->where('Report_ID', $id)
            ->first();
        
        if (!$report) {
            return response()->json(['message' => 'Report not found'], 404);
        }
        
        if (!$report->File_Path || !\Storage::disk('public')->exists($report->File_Path)) {
            return response()->json(['message' => 'Report file not found'], 404);
        }
        
        return \Storage::disk('public')->download($report->File_Path);
    }

    /**
     * @OA\Get(
     *     path="/api/patient/payments",
     *     summary="Get patient's payments",
     *     description="Retrieves all payment records for the authenticated patient",
     *     operationId="getPatientPayments",
     *     tags={"Patient Portal"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Payments retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Payment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Patient not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Patient not found")
     *         )
     *     )
     * )
     */
    public function myPayments()
    {
        $patient = $this->getAuthenticatedPatient();
        
        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }
        
        $payments = Payment::where('Patient_ID', $patient->Patient_ID)
            ->with(['test'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($payments);
    }

    /**
     * @OA\Get(
     *     path="/api/patient/orders/{id}/track",
     *     summary="Track order status",
     *     description="Retrieves the current status and tracking information for a specific order",
     *     operationId="trackPatientOrder",
     *     tags={"Patient Portal"},
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
     *         description="Order tracking information retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="order_id", type="integer", example=1),
     *             @OA\Property(property="order_status", type="string", example="Processing"),
     *             @OA\Property(property="sample_status", type="string", nullable=true, example="Collected"),
     *             @OA\Property(property="current_stage", type="string", example="Sample Under Analysis"),
     *             @OA\Property(property="order_date", type="string", format="date", example="2024-01-15")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order not found")
     *         )
     *     )
     * )
     */
    public function trackOrder($id)
    {
        $patient = $this->getAuthenticatedPatient();
        
        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }
        
        $order = Test::where('Patient_ID', $patient->Patient_ID)
            ->where('Order_ID', $id)
            ->with(['sample'])
            ->first();
        
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        
        $status = $order->Status;
        $sampleStatus = $order->sample ? $order->sample->Status : null;
        
        // تحديد المرحلة الحالية
        $stage = match ($status) {
            'Pending' => 'Order Placed',
            'Collected' => 'Sample Collected',
            'Processing' => 'Sample Under Analysis',
            'Completed' => 'Results Ready',
            default => 'Order Received'
        };
        
        return response()->json([
            'order_id' => $order->Order_ID,
            'order_status' => $status,
            'sample_status' => $sampleStatus,
            'current_stage' => $stage,
            'order_date' => $order->Order_Date,
        ]);
    }
}