<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{

    protected $fillable = [
        'patient_id',
        'order_id',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'status'
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

}
