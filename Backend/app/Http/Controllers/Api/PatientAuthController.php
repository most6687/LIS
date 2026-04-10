<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Patient Authentication",
 *     description="Patient authentication and registration endpoints"
 * )
 */
class PatientAuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/patient/register",
     *     summary="Register a new patient",
     *     description="Creates a new patient account with associated user credentials",
     *     operationId="registerPatient",
     *     tags={"Patient Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"Full_Name","Email","password"},
     *             @OA\Property(property="Full_Name", type="string", maxLength=100, example="John Doe"),
     *             @OA\Property(property="Email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="Phone", type="string", maxLength=20, nullable=true, example="+1234567890"),
     *             @OA\Property(property="Date_of_Birth", type="string", format="date", nullable=true, example="1990-01-01"),
     *             @OA\Property(property="Gender", type="string", enum={"M","F"}, nullable=true, example="M"),
     *             @OA\Property(property="Address", type="string", maxLength=200, nullable=true, example="123 Main St"),
     *             @OA\Property(property="Insurance_Info", type="string", maxLength=100, nullable=true, example="Insurance Company"),
     *             @OA\Property(property="password", type="string", minLength=6, example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Patient registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Patient registered successfully"),
     *             @OA\Property(property="token", type="string", example="1|abc123def456"),
     *             @OA\Property(property="user", ref="#/components/schemas/User"),
     *             @OA\Property(property="patient", ref="#/components/schemas/Patient")
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
    public function register(Request $request)
    {
        $request->validate([
            'Full_Name' => 'required|string|max:100',
            'Email' => 'required|email|unique:users,Email|unique:patients,Email',
            'Phone' => 'nullable|string|max:20',
            'Date_of_Birth' => 'nullable|date',
            'Gender' => 'nullable|in:M,F',
            'Address' => 'nullable|string|max:200',
            'Insurance_Info' => 'nullable|string|max:100',
            'password' => 'required|min:6|confirmed',
        ]);

        [$patient, $user] = DB::transaction(function () use ($request) {
            $patient = Patient::create([
                'Full_Name' => $request->Full_Name,
                'Email' => $request->Email,
                'Phone' => $request->Phone,
                'Date_of_Birth' => $request->Date_of_Birth,
                'Gender' => $request->Gender,
                'Address' => $request->Address,
                'Insurance_Info' => $request->Insurance_Info,
            ]);

            $user = User::create([
                'username' => $request->Email,
                'password' => $request->password,
                'Role' => 'Patient',
                'Full_Name' => $request->Full_Name,
                'Email' => $request->Email,
                'Phone' => $request->Phone,
                'Is_Active' => true,
            ]);

            return [$patient, $user];
        });

        // 3. ربط Patient بالـ User (لو عندك عمود user_id في patients)
        // $patient->update(['User_ID' => $user->User_ID]);

        $token = $user->createToken('patient_token')->plainTextToken;

        return response()->json([
            'message' => 'Patient registered successfully',
            'token' => $token,
            'user' => $user,
            'patient' => $patient
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/patient/login",
     *     summary="Patient login",
     *     description="Authenticates a patient and returns access token",
     *     operationId="loginPatient",
     *     tags={"Patient Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="1|abc123def456"),
     *             @OA\Property(property="user", ref="#/components/schemas/User"),
     *             @OA\Property(property="patient", ref="#/components/schemas/Patient")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid credentials")
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
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('Email', $request->email)
            ->where('Role', 'Patient')
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = $user->createToken('patient_token')->plainTextToken;

        // جلب بيانات المريض المرتبطة
        $patient = Patient::where('Email', $request->email)->first();

        return response()->json([
            'token' => $token,
            'user' => $user,
            'patient' => $patient
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/patient/logout",
     *     summary="Patient logout",
     *     description="Revokes the patient's access token",
     *     operationId="logoutPatient",
     *     tags={"Patient Authentication"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logged out successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logged out successfully")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
}