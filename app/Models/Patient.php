<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

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
}
