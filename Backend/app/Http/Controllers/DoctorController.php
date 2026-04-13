<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DoctorController extends Controller
{
    public function index()
    {
        return response()->json(Doctor::all());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Full_Name' => 'required|string|max:100',
            'Specialty' => 'nullable|string|max:100',
            'License_Number' => 'nullable|string|max:50',
            'Phone' => 'nullable|string|max:20',
            'Email' => 'nullable|email|max:100',
            'Clinic_Address' => 'nullable|string',
            'Is_External' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $doctor = Doctor::create($request->all());
        return response()->json($doctor, 201);
    }

    public function show($id)
    {
        $doctor = Doctor::findOrFail($id);
        return response()->json($doctor);
    }

    public function update(Request $request, $id)
    {
        $doctor = Doctor::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'Full_Name' => 'sometimes|required|string|max:100',
            'Specialty' => 'nullable|string|max:100',
            'License_Number' => 'nullable|string|max:50',
            'Phone' => 'nullable|string|max:20',
            'Email' => 'nullable|email|max:100',
            'Clinic_Address' => 'nullable|string',
            'Is_External' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $doctor->update($request->all());
        return response()->json($doctor);
    }

    public function destroy($id)
    {
        $doctor = Doctor::findOrFail($id);
        $doctor->delete();
        return response()->json(['message' => 'Doctor deleted']);
    }
}
