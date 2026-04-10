<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sample extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'samples';
    protected $primaryKey = 'Sample_ID';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'Order_ID',
        'User_ID',
        'Sample_Type',
        'Collection_Date',
        'Status',
        'Storage_Location',
        'Expiration_Date',
        'Container_Type',
        'Volume',
    ];

    protected $casts = [
        'Collection_Date' => 'datetime',
        'Expiration_Date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function test()
    {
        return $this->belongsTo(Test::class, 'Order_ID', 'Order_ID');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'User_ID', 'User_ID');
    }

    public function scopePending($query)
    {
        return $query->where('Status', 'Pending');
    }

    public function scopeCollected($query)
    {
        return $query->where('Status', 'Collected');
    }

    public function scopeInAnalysis($query)
    {
        return $query->where('Status', 'In_Analysis');
    }

    public function scopeCompleted($query)
    {
        return $query->where('Status', 'Completed');
    }

    public function scopeExpired($query)
    {
        return $query->where('Expiration_Date', '<', now());
    }

    public function isExpired()
    {
        return $this->Expiration_Date && $this->Expiration_Date < now();
    }

    public function isCompleted()
    {
        return $this->Status === 'Completed';
    }

    public function getStorageInfoAttribute()
    {
        return $this->Storage_Location . ' - ' . $this->Container_Type;
    }
}
