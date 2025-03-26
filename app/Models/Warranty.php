<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Warranty extends Model {

    use HasFactory;

    // Define the table name explicitly (optional if following Laravel conventions)
    protected $table = 'warranties';
    // Define primary key properties
    protected $primaryKey = 'warrantyNo';
    public $incrementing = false; // Since it's a string, not an auto-incremented integer
    protected $keyType = 'string';
    // Define mass assignable attributes
    protected $fillable = [
        'warrantyNo',
        'startDate',
        'endDate',
        'duration',
        'durationUnit',
        'status',
        'description',
    ];
    // Define date attributes to be treated as Carbon instances
    protected $casts = [
        'endDate' => 'date',
        'startDate' => 'date',
    ];

    /**
     * Relationship: Warranty belongs to a Task.
     */
    public function task() {
        return $this->hasOne(Task::class, 'warrantyNo', 'warrantyNo');
    }

    /**
     * Relationship: Warranty has many Warranty Requests.
     */
    public function warrantyRequests() {
        return $this->hasMany(WarrantyRequest::class, 'warrantyNo', 'warrantyNo');
    }

    /**
     * Check if the warranty is expired.
     */
    public function isExpired(): bool {
        return Carbon::now()->gt($this->endDate);
    }
}
