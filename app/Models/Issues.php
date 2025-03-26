<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Issues extends Model {

    protected $table = 'issues';
    protected $primaryKey = 'issuesID';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'issuesID',
        'issuesName',
        'issueHandler',
        'issuesStatus',
        'severity',
        'budget',
        'dueDate',
        'requestID',
        'serviceNo', // Foreign key to service_reports
    ];
    protected $casts = [
        'dueDate' => 'date',
    ];

    // Define the relationship with WarrantyRequest
    public function warrantyRequest() {
        return $this->belongsTo(WarrantyRequest::class, 'requestID', 'requestID');
    }

    // Define the relationship with ServiceReport
    public function serviceReport() {
        return $this->belongsTo(ServiceReport::class, 'serviceNo', 'serviceNo');
    }
}
