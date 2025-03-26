<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model {

    protected $primaryKey = 'invoiceNo';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'invoiceNo',
        'subtotal', 'taxRate', 'totalAmount', 'depositRate',
        'depositAmount', 'dueDate', 'paymentInstruction', 'reportID',
        'previousAmount', 'balance'
    ];
    protected $dates = ['dueDate'];

    // Relationship with Report
    public function report() {
        return $this->belongsTo(Report::class, 'reportID', 'reportID');
    }

    // Relationship with Tasks via TaskReport
    public function tasks() {
        return $this->belongsToMany(
                        Task::class,
                        TaskReport::class,
                        'invoiceNo', // Foreign key on TaskReport table
                        'taskID', // Foreign key on Task table
                        'invoiceNo', // Local key on Invoice table
                        'taskID' // Local key on TaskReport table
        );
    }

    // Relationship with Payments
    public function payments() {
        return $this->hasMany(Payment::class, 'invoiceNo', 'invoiceNo');
    }
}
