<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Report",
 *     title="Report",
 *     description="Medical report model",
 *     @OA\Property(property="Report_ID", type="integer", example=1),
 *     @OA\Property(property="Patient_ID", type="integer", example=1),
 *     @OA\Property(property="Doctor_ID", type="integer", example=1),
 *     @OA\Property(property="User_ID", type="integer", example=1),
 *     @OA\Property(property="Order_ID", type="integer", example=1),
 *     @OA\Property(property="Generated_Date", type="string", format="date", example="2024-01-15"),
 *     @OA\Property(property="Type", type="string", example="Blood Test Report"),
 *     @OA\Property(property="Report_Status", type="string", enum={"Draft","Final","Approved"}, example="Final"),
 *     @OA\Property(property="Report_Format", type="string", enum={"PDF","DOC","HTML"}, example="PDF"),
 *     @OA\Property(property="File_Path", type="string", example="reports/report_1.pdf"),
 *     @OA\Property(property="Notes", type="string", example="Normal results"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
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

    // Scopes
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