<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doctor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'doctors';
    protected $primaryKey = 'Doctor_ID';

    protected $fillable = [
        'Full_Name',
        'Specialty',
        'License_Number',
        'Phone',
        'Email',
        'Clinic_Address',
        'Is_External',
    ];

    protected $casts = [
        'Is_External' => 'boolean',
    ];

    public function patients()
    {
        return $this->belongsToMany(Patient::class, 'doctor_patient', 'Doctor_ID', 'Patient_ID')
                    ->withTimestamps();
    }

    public function tests()
    {
        return $this->hasMany(Test::class, 'Doctor_ID', 'Doctor_ID');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'Doctor_ID', 'Doctor_ID');
    }
}
