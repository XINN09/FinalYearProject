<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WarrantyRequest extends Model
{
    use HasFactory;

    // Define the table name (optional if it follows Laravel's naming conventions)
    protected $table = 'warranty_requests';

    // Set primary key (if it's not the default 'id')
    protected $primaryKey = 'requestID';
    public $incrementing = false; // Since it's a string, not an auto-incremented integer
    protected $keyType = 'string';

    // Define fillable fields to allow mass assignment
    protected $fillable = [
        'requestID',
        'requestTitle',
        'requesterName', 
        'requestDate',
        'requestDesc',
        'requestStatus',
        'warrantyNo',
        'reportContent',
    ];

    // Define the relationship with the Warranty model
    public function warranty()
    {
        return $this->belongsTo(Warranty::class, 'warrantyNo', 'warrantyNo');
    }
}
