<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Test;
use App\Models\Patient;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Tag(
 *     name="Reports",
 *     description="Manage laboratory test reports"
 * )
 */
class ReportController extends Controller
{
    /**
     * List all reports
     *
     * @OA\Get(
     *     path="/api/reports",
     *     operationId="listReports",
     *     tags={"Reports"},
     *     summary="Get all reports",
     *     description="Retrieve all laboratory test reports with related patient, doctor, user and test information",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of all reports",
     *         @OA\JsonContent(
     *             type="array",
     *             items=@OA\Items(
     *                 @OA\Property(property="Report_ID", type="integer", example=1),
     *                 @OA\Property(property="Patient_ID", type="integer", example=1),
     *                 @OA\Property(property="Doctor_ID", type="integer", nullable=true, example=5),
     *                 @OA\Property(property="User_ID", type="integer", example=2),
     *                 @OA\Property(property="Order_ID", type="integer", example=1),
     *                 @OA\Property(property="Generated_Date", type="string", format="date-time", example="2026-02-15T14:30:00Z"),
     *                 @OA\Property(property="Type", type="string", enum={"Preliminary","Final"}, example="Final"),
     *                 @OA\Property(property="Report_Status", type="string", enum={"Draft","Finalized"}, example="Draft"),
     *                 @OA\Property(property="Report_Format", type="string", enum={"Digital","Pdf"}, example="Digital"),
     *                 @OA\Property(property="File_Path", type="string", nullable=true, example="reports/report_001.pdf"),
     *                 @OA\Property(property="Notes", type="string", nullable=true, example="First report"),
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
        $reports = Report::with(['patient', 'doctor', 'user', 'test'])->get();
        return response()->json($reports);
    }

    /**
     * Get reports by status
     *
     * @OA\Get(
     *     path="/api/reports/status/{status}",
     *     operationId="getReportsByStatus",
     *     tags={"Reports"},
     *     summary="Get reports by status",
     *     description="Retrieve reports filtered by status (Draft or Finalized)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="path",
     *         required=true,
     *         description="Report status filter",
     *         @OA\Schema(
     *             type="string",
     *             enum={"Draft","Finalized"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reports matching the specified status",
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
        $statuses = ['Draft', 'Finalized'];
        if (!in_array($status, $statuses)) {
            return response()->json(['message' => 'Invalid status'], 400);
        }

        $reports = Report::where('Report_Status', $status)
            ->with(['patient', 'doctor', 'user'])
            ->get();
        return response()->json($reports);
    }

    /**
     * Get reports by type
     *
     * @OA\Get(
     *     path="/api/reports/type/{type}",
     *     operationId="getReportsByType",
     *     tags={"Reports"},
     *     summary="Get reports by type",
     *     description="Retrieve reports filtered by type (Preliminary or Final)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="path",
     *         required=true,
     *         description="Report type filter",
     *         @OA\Schema(
     *             type="string",
     *             enum={"Preliminary","Final"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reports of the specified type",
     *         @OA\JsonContent(type="array", items=@OA\Items())
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid type"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function byType($type)
    {
        $types = ['Preliminary', 'Final'];
        if (!in_array($type, $types)) {
            return response()->json(['message' => 'Invalid type'], 400);
        }

        $reports = Report::where('Type', $type)
            ->with(['patient', 'doctor', 'user'])
            ->get();
        return response()->json($reports);
    }

    /**
     * Get reports for a specific patient
     *
     * @OA\Get(
     *     path="/api/reports/patient/{patientId}",
     *     operationId="getReportsByPatient",
     *     tags={"Reports"},
     *     summary="Get patient reports",
     *     description="Retrieve all reports for a specific patient",
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
     *         description="Reports for the specified patient",
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

        $reports = Report::where('Patient_ID', $patientId)
            ->with(['doctor', 'user', 'test'])
            ->get();
        return response()->json($reports);
    }

    /**
     * Get a single report
     *
     * @OA\Get(
     *     path="/api/reports/{id}",
     *     operationId="getReport",
     *     tags={"Reports"},
     *     summary="Get report details",
     *     description="Retrieve detailed information for a specific report",
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
     *         description="Report details",
     *         @OA\JsonContent(
     *             @OA\Property(property="Report_ID", type="integer"),
     *             @OA\Property(property="Patient_ID", type="integer"),
     *             @OA\Property(property="Type", type="string"),
     *             @OA\Property(property="Report_Status", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Report not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function show($id)
    {
        $report = Report::with(['patient', 'doctor', 'user', 'test'])->findOrFail($id);
        return response()->json($report);
    }

    /**
     * Create a new report
     *
     * @OA\Post(
     *     path="/api/reports",
     *     operationId="createReport",
     *     tags={"Reports"},
     *     summary="Create a new report",
     *     description="Create a new laboratory report from a test order",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Report data",
     *         @OA\JsonContent(
     *             required={"Order_ID"},
     *             @OA\Property(property="Order_ID", type="integer", description="Test Order ID", example=1),
     *             @OA\Property(property="Type", type="string", enum={"Preliminary","Final"}, description="Report type", example="Final"),
     *             @OA\Property(property="Report_Format", type="string", enum={"Digital","Pdf"}, description="Report format", example="Digital"),
     *             @OA\Property(property="Notes", type="string", nullable=true, description="Additional notes", example="Complete analysis")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Report created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Report created successfully"),
     *             @OA\Property(property="report", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Report already exists for this order"
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
            'Type' => 'nullable|in:Preliminary,Final',
            'Report_Format' => 'nullable|in:Digital,Pdf',
            'Notes' => 'nullable|string',
        ]);

        $test = Test::findOrFail($request->Order_ID);

        // اتأكد إن مفيش تقرير موجود بالفعل
        $existingReport = Report::where('Order_ID', $request->Order_ID)->first();
        if ($existingReport) {
            return response()->json([
                'message' => 'Report already exists for this order'
            ], 409);
        }

        $report = Report::create([
            'Patient_ID' => $test->Patient_ID,
            'Doctor_ID' => $test->Doctor_ID,
            'User_ID' => Auth::user()->User_ID,
            'Order_ID' => $request->Order_ID,
            'Generated_Date' => now(),
            'Type' => $request->Type ?? 'Final',
            'Report_Status' => 'Draft',
            'Report_Format' => $request->Report_Format ?? 'Digital',
            'Notes' => $request->Notes,
        ]);

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Module' => 'Reports',
            'Action' => 'Report Created',
            'Description' => 'Report created for Order #' . $request->Order_ID
        ]);

        return response()->json([
            'message' => 'Report created successfully',
            'report' => $report
        ], 201);
    }

    /**
     * Update a report
     *
     * @OA\Put(
     *     path="/api/reports/{id}",
     *     operationId="updateReport",
     *     tags={"Reports"},
     *     summary="Update report",
     *     description="Update report information",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Report ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Report update data",
     *         @OA\JsonContent(
     *             @OA\Property(property="Notes", type="string", nullable=true, example="Updated analysis"),
     *             @OA\Property(property="Type", type="string", enum={"Preliminary","Final"}, example="Final"),
     *             @OA\Property(property="Report_Format", type="string", enum={"Digital","Pdf"}, example="Pdf")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Report updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="report", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Report not found"
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
        $report = Report::findOrFail($id);

        $request->validate([
            'Notes' => 'nullable|string',
            'Type' => 'nullable|in:Preliminary,Final',
            'Report_Format' => 'nullable|in:Digital,Pdf',
        ]);

        $report->update($request->only(['Notes', 'Type', 'Report_Format']));

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Module' => 'Reports',
            'Action' => 'Report Updated',
            'Description' => 'Report #' . $id . ' updated'
        ]);

        return response()->json([
            'message' => 'Report updated successfully',
            'report' => $report
        ]);
    }

    /**
     * Finalize a report
     *
     * @OA\Put(
     *     path="/api/reports/{id}/finalize",
     *     operationId="finalizeReport",
     *     tags={"Reports"},
     *     summary="Finalize a report",
     *     description="Mark a report as finalized (approved)",
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
     *         description="Report finalized successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="report", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Report not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Report is already finalized"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function finalize($id)
    {
        $report = Report::findOrFail($id);

        if ($report->Report_Status === 'Finalized') {
            return response()->json([
                'message' => 'Report is already finalized'
            ], 422);
        }

        $report->update(['Report_Status' => 'Finalized']);

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Module' => 'Reports',
            'Action' => 'Report Finalized',
            'Description' => 'Report #' . $id . ' has been finalized'
        ]);

        return response()->json([
            'message' => 'Report finalized successfully',
            'report' => $report
        ]);
    }

    /**
     * Upload report file
     *
     * @OA\Post(
     *     path="/api/reports/{id}/upload",
     *     operationId="uploadReportFile",
     *     tags={"Reports"},
     *     summary="Upload report file",
     *     description="Upload a PDF file for a report (max 5MB)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Report ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="PDF file upload",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(property="file", type="string", format="binary", description="PDF file (max 5MB)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="file_url", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Report not found"
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
    public function uploadFile(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:5120' // max 5MB
        ]);

        $report = Report::findOrFail($id);

        // حذف الملف القديم لو موجود
        if ($report->File_Path && Storage::disk('public')->exists($report->File_Path)) {
            Storage::disk('public')->delete($report->File_Path);
        }

        $path = $request->file('file')->store('reports', 'public');
        $report->update(['File_Path' => $path]);

        return response()->json([
            'message' => 'Report file uploaded successfully',
            'file_url' => Storage::url($path)
        ]);
    }

    /**
     * Download report file
     *
     * @OA\Get(
     *     path="/api/reports/{id}/download",
     *     operationId="downloadReportFile",
     *     tags={"Reports"},
     *     summary="Download report file",
     *     description="Download the PDF file attached to a report",
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
     *         description="PDF file",
     *         @OA\MediaType(
     *             mediaType="application/pdf"
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Report or file not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function download($id)
    {
        $report = Report::findOrFail($id);

        if (!$report->File_Path) {
            return response()->json([
                'message' => 'No file attached to this report'
            ], 404);
        }

        if (!Storage::disk('public')->exists($report->File_Path)) {
            return response()->json([
                'message' => 'File not found'
            ], 404);
        }

        return Storage::disk('public')->download($report->File_Path);
    }

    /**
     * Delete a report
     *
     * @OA\Delete(
     *     path="/api/reports/{id}",
     *     operationId="deleteReport",
     *     tags={"Reports"},
     *     summary="Delete a report",
     *     description="Delete a report and its attached file",
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
     *         description="Report deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Report deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Report not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function destroy($id)
    {
        $report = Report::findOrFail($id);

        // حذف الملف المرتبط لو موجود
        if ($report->File_Path && Storage::disk('public')->exists($report->File_Path)) {
            Storage::disk('public')->delete($report->File_Path);
        }

        $report->delete();

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Module' => 'Reports',
            'Action' => 'Report Deleted',
            'Description' => 'Report #' . $id . ' deleted'
        ]);

        return response()->json(['message' => 'Report deleted successfully']);
    }
}