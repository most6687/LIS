<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Result extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'results';
    protected $primaryKey = 'Result_ID';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'User_ID',
        'Sample_ID',
        'Result_Value',
        'Unit',
        'Normal_Range',
        'Test_Name',
        'Method_Used',
        'Interpretation',
        'Status',
        'Result_Date',
    ];

    protected $casts = [
        'Result_Date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'User_ID', 'User_ID');
    }

    public function sample()
    {
        return $this->belongsTo(Sample::class, 'Sample_ID', 'Sample_ID');
    }

    public function test()
    {
        return $this->hasOneThrough(
            Test::class,
            Sample::class,
            'Sample_ID',
            'Order_ID',
            'Sample_ID',
            'Order_ID'
        );
    }

    public function patient()
    {
        return $this->hasOneThrough(
            Patient::class,
            Test::class,
            'Order_ID',
            'Patient_ID',
            'test.Order_ID',
            'Patient_ID'
        );
    }

    public function doctor()
    {
        return $this->hasOneThrough(
            Doctor::class,
            Test::class,
            'Order_ID',
            'Doctor_ID',
            'test.Order_ID',
            'Doctor_ID'
        );
    }
}
