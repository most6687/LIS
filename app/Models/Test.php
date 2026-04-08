<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Test extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tests';
    protected $primaryKey = 'Order_ID';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'Patient_ID',
        'Doctor_ID',
        'User_ID',
        'Order_Date',
        'Priority',
        'Status',
        'Total_Amount',
        'Requested_Tests',
        'Notes',
    ];

    protected $casts = [
        'Order_Date' => 'datetime',
        'Total_Amount' => 'decimal:2',
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

    public function sample()
    {
        return $this->hasOne(Sample::class, 'Order_ID', 'Order_ID');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'Order_ID', 'Order_ID');
    }

    public function report()
    {
        return $this->hasOne(Report::class, 'Order_ID', 'Order_ID');
    }

    public function scopePending($query)
    {
        return $query->where('Status', 'Pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('Status', 'Completed');
    }

    public function scopeUrgent($query)
    {
        return $query->where('Priority', 'Urgent');
    }

    public function getRequestedTestsArrayAttribute()
    {
        return $this->Requested_Tests ? explode(',', $this->Requested_Tests) : [];
    }

    public function isCompleted()
    {
        return $this->Status === 'Completed';
    }

    public function isUrgent()
    {
        return $this->Priority === 'Urgent';
    }
}
