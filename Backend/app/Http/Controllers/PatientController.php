<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Patients",
 *     description="API endpoints for managing patients"
 * )
 */
class PatientController extends Controller
{
    /**
     * Get all patients
     *
     * @OA\Get(
     *     path="/api/patients",
     *     operationId="getPatients",
     *     tags={"Patients"},
     *     summary="Retrieve all patients",
     *     description="Get a list of all patients in the system",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="Patient_ID", type="integer", example=1),
     *                 @OA\Property(property="Full_Name", type="string", example="Ahmed Al-Salimi"),
     *                 @OA\Property(property="Gender", type="string", enum={"M", "F"}, example="M"),
     *                 @OA\Property(property="Date_of_Birth", type="string", format="date", example="1990-05-15"),
     *                 @OA\Property(property="Phone", type="string", example="+966501234567"),
     *                 @OA\Property(property="Address", type="string", example="Riyadh, Saudi Arabia"),
     *                 @OA\Property(property="Email", type="string", format="email", example="patient@example.com"),
     *                 @OA\Property(property="Insurance_Info", type="string", example="Policy #123456"),
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
        return response()->json(Patient::all());
    }

    /**
     * Create a new patient
     *
     * @OA\Post(
     *     path="/api/patients",
     *     operationId="storePatient",
     *     tags={"Patients"},
     *     summary="Create a new patient",
     *     description="Store a newly created patient in the database",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Patient data",
     *         @OA\JsonContent(
     *             required={"Full_Name"},
     *             @OA\Property(property="Full_Name", type="string", maxLength=100, example="Mohamed Ahmed"),
     *             @OA\Property(property="Gender", type="string", enum={"M", "F"}, nullable=true, example="M"),
     *             @OA\Property(property="Date_of_Birth", type="string", format="date", nullable=true, example="1985-03-20"),
     *             @OA\Property(property="Phone", type="string", maxLength=20, nullable=true, example="+966501234567"),
     *             @OA\Property(property="Address", type="string", maxLength=200, nullable=true, example="Jeddah, Saudi Arabia"),
     *             @OA\Property(property="Email", type="string", format="email", maxLength=100, nullable=true, example="patient@example.com"),
     *             @OA\Property(property="Insurance_Info", type="string", maxLength=100, nullable=true, example="Policy #789012")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Patient created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="Patient_ID", type="integer", example=1),
     *             @OA\Property(property="Full_Name", type="string", example="Mohamed Ahmed"),
     *             @OA\Property(property="Gender", type="string", example="M"),
     *             @OA\Property(property="Date_of_Birth", type="string", format="date"),
     *             @OA\Property(property="Phone", type="string"),
     *             @OA\Property(property="Address", type="string"),
     *             @OA\Property(property="Email", type="string"),
     *             @OA\Property(property="Insurance_Info", type="string"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
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
    public function store(StorePatientRequest $request)
    {
        $patient = Patient::create($request->validated());

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Module' => 'Patients',
            'Action' => 'Created Patient',
            'Description' => 'New patient added to system'
        ]);

        return response()->json($patient, 201);
    }

    /**
     * Get a specific patient
     *
     * @OA\Get(
     *     path="/api/patients/{id}",
     *     operationId="getPatient",
     *     tags={"Patients"},
     *     summary="Retrieve a patient by ID",
     *     description="Get details of a specific patient",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Patient ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="Patient_ID", type="integer", example=1),
     *             @OA\Property(property="Full_Name", type="string", example="Ahmed Al-Salimi"),
     *             @OA\Property(property="Gender", type="string", enum={"M", "F"}),
     *             @OA\Property(property="Date_of_Birth", type="string", format="date"),
     *             @OA\Property(property="Phone", type="string"),
     *             @OA\Property(property="Address", type="string"),
     *             @OA\Property(property="Email", type="string"),
     *             @OA\Property(property="Insurance_Info", type="string"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time"),
     *             @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
     *         )
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
    public function show($id)
    {
        $patient = Patient::findOrFail($id);
        return response()->json($patient);
    }

    /**
     * Update a patient
     *
     * @OA\Put(
     *     path="/api/patients/{id}",
     *     operationId="updatePatient",
     *     tags={"Patients"},
     *     summary="Update a patient",
     *     description="Update details of an existing patient",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Patient ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Updated patient data",
     *         @OA\JsonContent(
     *             @OA\Property(property="Full_Name", type="string", maxLength=100, example="Mohamed Ahmed Updated"),
     *             @OA\Property(property="Gender", type="string", enum={"M", "F"}, nullable=true, example="M"),
     *             @OA\Property(property="Date_of_Birth", type="string", format="date", nullable=true, example="1985-03-20"),
     *             @OA\Property(property="Phone", type="string", maxLength=20, nullable=true, example="+966501234567"),
     *             @OA\Property(property="Address", type="string", maxLength=200, nullable=true, example="Dammam, Saudi Arabia"),
     *             @OA\Property(property="Email", type="string", format="email", maxLength=100, nullable=true, example="newemail@example.com"),
     *             @OA\Property(property="Insurance_Info", type="string", maxLength=100, nullable=true, example="Policy #789012")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Patient updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="Patient_ID", type="integer", example=1),
     *             @OA\Property(property="Full_Name", type="string"),
     *             @OA\Property(property="Gender", type="string"),
     *             @OA\Property(property="Date_of_Birth", type="string", format="date"),
     *             @OA\Property(property="Phone", type="string"),
     *             @OA\Property(property="Address", type="string"),
     *             @OA\Property(property="Email", type="string"),
     *             @OA\Property(property="Insurance_Info", type="string"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Patient not found"
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
    public function update(UpdatePatientRequest $request, $id)
    {
        $patient = Patient::findOrFail($id);

        $patient->update($request->validated());

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Action' => 'Updated Patient',
            'Module' => 'Patients',
            'Description' => 'Patient data updated'
        ]);

        return response()->json($patient);
    }

    /**
     * Delete a patient
     *
     * @OA\Delete(
     *     path="/api/patients/{id}",
     *     operationId="destroyPatient",
     *     tags={"Patients"},
     *     summary="Delete a patient",
     *     description="Soft delete a patient record (moves to trash)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Patient ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Patient deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Patient deleted")
     *         )
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
    public function destroy($id)
    {
        $patient = Patient::findOrFail($id);

        $patient->delete();

        ActivityLog::create([
            'User_ID' => Auth::user()->User_ID,
            'Action' => 'Deleted Patient',
            'Module' => 'Patients',
            'Description' => 'Patient removed from system'
        ]);

        return response()->json(['message' => 'Patient deleted']);
    }
}
