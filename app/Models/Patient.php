<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

/**
 * @OA\Schema(
 *     schema="Patient",
 *     title="Patient",
 *     description="Patient model",
 *     @OA\Property(property="Patient_ID", type="integer", example=1),
 *     @OA\Property(property="Full_Name", type="string", example="John Doe"),
 *     @OA\Property(property="Gender", type="string", enum={"M","F"}, example="M"),
 *     @OA\Property(property="Date_of_Birth", type="string", format="date", example="1990-01-01"),
 *     @OA\Property(property="Phone", type="string", example="+1234567890"),
 *     @OA\Property(property="Address", type="string", example="123 Main St"),
 *     @OA\Property(property="Email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="Insurance_Info", type="string", example="Insurance Company"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Patient extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'patients';
    protected $primaryKey = 'Patient_ID';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'Full_Name',
        'Gender',
        'Date_of_Birth',
        'Phone',
        'Address',
        'Email',
        'Insurance_Info',
    ];

    protected $casts = [
        'Date_of_Birth' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function doctors()
    {
        return $this->belongsToMany(Doctor::class, 'doctor_patient', 'Patient_ID', 'Doctor_ID')
            ->withTimestamps();
    }

    public function tests()
    {
        return $this->hasMany(Test::class, 'Patient_ID', 'Patient_ID');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'Patient_ID', 'Patient_ID');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'Patient_ID', 'Patient_ID');
    }

    public function getAgeAttribute()
    {
        if (!$this->Date_of_Birth) {
            return null;
        }
        return Carbon::parse($this->Date_of_Birth)->age;
    }

    public function getNameAttribute()
    {
        return $this->Full_Name;
    }
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'patient_id', 'Patient_ID');
    }
}
