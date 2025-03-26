<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model {

    protected $primaryKey = 'reportID';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'reportID',
        'reportDate',
        'remarks',
        'projectID'
    ];

    // Relationship with Project
    public function project(): BelongsTo {
        return $this->belongsTo(Project::class, 'projectID', 'projectID');
    }

    public function invoices() {
        return $this->hasMany(Invoice::class, 'reportID', 'reportID');
    }

    public function quotations() {
        return $this->hasMany(Quotation::class, 'reportID', 'reportID');
    }

    public function payments() {
        return $this->hasManyThrough(
                        Payment::class,
                        Invoice::class,
                        'reportID', // Foreign key on Invoice table
                        'invoiceNo', // Foreign key on Payment table
                        'reportID', // Local key on Report table
                        'invoiceNo' // Local key on Invoice table
                )->union(
                        $this->hasManyThrough(
                                Payment::class,
                                Quotation::class,
                                'reportID', // Foreign key on Quotation table
                                'quotationNo', // Foreign key on Payment table
                                'reportID', // Local key on Report table
                                'quotationNo' // Local key on Quotation table
                        )
        );
    }
}
