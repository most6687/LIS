<?php

namespace App\Http\Controllers;


use App\Models\Patient;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    // عرض كل المرضى
    public function index()
    {
        return response()->json(Patient::all());
    }

    // إضافة مريض جديد
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
    // عرض مريض واحد
    public function show($id)
    {
        $patient = Patient::findOrFail($id);
        return response()->json($patient);
    }

    // تحديث بيانات مريض
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

    // حذف مريض (Soft Delete)
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
