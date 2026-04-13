<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Inventory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventory';
    protected $primaryKey = 'Item_ID';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'Item_Name',
        'Quantity',
        'Min_Level',
        'Expiry_Date',
        'Supplier_Info',
        'Category',
        'Last_Restock_Date',
        'Needs_Restock',
        'Storage_Location',
        'Unit_Price',
    ];

    protected $casts = [
        'Quantity' => 'integer',
        'Min_Level' => 'integer',
        'Expiry_Date' => 'date',
        'Last_Restock_Date' => 'date',
        'Needs_Restock' => 'boolean',
        'Unit_Price' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function scopeNeedsRestock($query)
    {
        return $query->where('Needs_Restock', true)
                     ->orWhereRaw('Quantity <= Min_Level');
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('Quantity <= Min_Level');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('Quantity', '<=', 0);
    }

    public function scopeExpired($query)
    {
        return $query->whereDate('Expiry_Date', '<', Carbon::today());
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->whereDate('Expiry_Date', '>=', Carbon::today())
                     ->whereDate('Expiry_Date', '<=', Carbon::today()->addDays($days));
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('Category', $category);
    }

    public function isLowStock()
    {
        return $this->Quantity <= $this->Min_Level;
    }

    public function isOutOfStock()
    {
        return $this->Quantity <= 0;
    }

    public function isExpired()
    {
        return $this->Expiry_Date && Carbon::parse($this->Expiry_Date)->lt(Carbon::today());
    }

    public function isExpiringSoon($days = 30)
    {
        return $this->Expiry_Date &&
               Carbon::parse($this->Expiry_Date)->gte(Carbon::today()) &&
               Carbon::parse($this->Expiry_Date)->lte(Carbon::today()->addDays($days));
    }

    public function getStockStatusAttribute()
    {
        if ($this->isOutOfStock()) {
            return 'Out of Stock';
        }
        if ($this->isLowStock()) {
            return 'Low Stock';
        }
        return 'In Stock';
    }

    public function getStockStatusColorAttribute()
    {
        if ($this->isOutOfStock()) {
            return 'danger';
        }
        if ($this->isLowStock()) {
            return 'warning';
        }
        if ($this->isExpired()) {
            return 'dark';
        }
        if ($this->isExpiringSoon()) {
            return 'info';
        }
        return 'success';
    }

    public function getTotalValueAttribute()
    {
        return $this->Quantity * $this->Unit_Price;
    }

    public function restock($quantity)
    {
        $this->Quantity += $quantity;
        $this->attributes['Last_Restock_Date'] = Carbon::today()->toDateString();
        $this->Needs_Restock = false;
        $this->save();
    }

    public function consume($quantity)
    {
        if ($this->Quantity >= $quantity) {
            $this->Quantity -= $quantity;
            $this->Needs_Restock = $this->isLowStock();
            $this->save();
            return true;
        }
        return false;
    }
}
