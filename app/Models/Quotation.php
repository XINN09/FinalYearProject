<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model {

    protected $primaryKey = 'quotationNo';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'quotationNo',
        'subtotal',
        'taxRate',
        'estimatedCost',
        'depositRate',
        'depositAmount',
        'validityStart',
        'validityEnd',
        'paymentInstruction',
        'reportID',
        'previousAmount', 'balance'
    ];

    // Relationship with Report
    public function report() {
        return $this->belongsTo(Report::class, 'reportID', 'reportID');
    }

    // Relationship with Tasks via TaskReport
    public function tasks() {
        return $this->belongsToMany(
                        Task::class,
                        TaskReport::class,
                        'quotationNo', // Foreign key on TaskReport table
                        'taskID', // Foreign key on Task table
                        'quotationNo', // Local key on Quotation table
                        'taskID' // Local key on TaskReport table
        );
    }

    // Relationship with Payments
    public function payments() {
        return $this->hasMany(Payment::class, 'quotationNo', 'quotationNo');
    }
}
