<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'payments';
    protected $primaryKey = 'Payment_ID';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'Order_ID',
        'Patient_ID',
        'User_ID',
        'Payment_Date',
        'Amount',
        'Payment_Method',
        'Payment_Status',
        'Transaction_ID',
        'Invoice_Number',
        'Billing_Date',
        'Due_Date',
        'Notes',
    ];

    protected $casts = [
        'Payment_Date' => 'datetime',
        'Amount' => 'decimal:2',
        'Billing_Date' => 'date',
        'Due_Date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function test()
    {
        return $this->belongsTo(Test::class, 'Order_ID', 'Order_ID');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'Patient_ID', 'Patient_ID');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'User_ID', 'User_ID');
    }

    public function scopePaid($query)
    {
        return $query->where('Payment_Status', 'Paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('Payment_Status', 'Unpaid');
    }

    public function scopePartial($query)
    {
        return $query->where('Payment_Status', 'Partial');
    }

    public function scopeCash($query)
    {
        return $query->where('Payment_Method', 'Cash');
    }

    public function scopeCard($query)
    {
        return $query->where('Payment_Method', 'Card');
    }

    public function scopeElectronic($query)
    {
        return $query->where('Payment_Method', 'Electronic');
    }

    public function scopeOverdue($query)
    {
        return $query->where('Due_Date', '<', now())
                     ->where('Payment_Status', '!=', 'Paid');
    }

    public function isPaid()
    {
        return $this->Payment_Status === 'Paid';
    }

    public function isUnpaid()
    {
        return $this->Payment_Status === 'Unpaid';
    }

    public function isPartial()
    {
        return $this->Payment_Status === 'Partial';
    }

    public function isOverdue()
    {
        return $this->Due_Date && $this->Due_Date < now() && !$this->isPaid();
    }

    public function getRemainingAmountAttribute()
    {
        if ($this->isPaid()) {
            return 0;
        }

        if ($this->isPartial()) {
            $test = $this->test;
            return $test ? $test->Total_Amount - $this->Amount : $this->Amount;
        }

        return $this->test ? $this->test->Total_Amount : $this->Amount;
    }

    public function getInvoiceInfoAttribute()
    {
        return $this->Invoice_Number ?: 'INV-' . str_pad($this->Payment_ID, 6, '0', STR_PAD_LEFT);
    }
}
