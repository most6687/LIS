<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'reports';
    protected $primaryKey = 'Report_ID';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'Patient_ID',
        'Doctor_ID',
        'User_ID',
        'Order_ID',
        'Generated_Date',
        'Type',
        'Report_Status',
        'Report_Format',
        'File_Path',
        'Notes',
    ];

    protected $casts = [
        'Generated_Date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'Patient_ID', 'Patient_ID');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'Doctor_ID', 'User_ID');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'User_ID', 'User_ID');
    }

    public function test()
    {
        return $this->belongsTo(Test::class, 'Order_ID', 'Order_ID');
    }

    public function scopeDraft($query)
    {
        return $query->where('Report_Status', 'Draft');
    }

    public function scopeFinalized($query)
    {
        return $query->where('Report_Status', 'Finalized');
    }

    public function scopePreliminary($query)
    {
        return $query->where('Type', 'Preliminary');
    }

    public function scopeFinal($query)
    {
        return $query->where('Type', 'Final');
    }

    public function isFinalized()
    {
        return $this->Report_Status === 'Finalized';
    }

    public function isPreliminary()
    {
        return $this->Type === 'Preliminary';
    }

    public function getFilePathUrlAttribute()
    {
        return $this->File_Path ? asset('storage/' . $this->File_Path) : null;
    }
}
