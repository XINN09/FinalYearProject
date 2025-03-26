<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model {
    protected $table = 'tasks';
    protected $primaryKey = 'taskID';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'taskID',
        'taskName',
        'status',
        'startDate',
        'endDate',
        'duration',
        'durationUnit',
        'priority',
        'qty',
        'uom',
        'unitPrice',
        'budget',
        'remarks',
        'projectID',
        'warrantyNo',
    ];

    protected $casts = [
        'startDate' => 'date',
        'endDate' => 'date',
        'unitPrice' => 'decimal:2',
        'budget' => 'decimal:2',
        'duration' => 'float',
    ];

    // Relationship with Project
    public function project() {
        return $this->belongsTo(Project::class, 'projectID', 'projectID');
    }

    // Relationship with Warranty
    public function warranty() {
        return $this->belongsTo(Warranty::class, 'warrantyNo', 'warrantyNo');
    }

    // Relationship with Assignments
    public function assignments() {
        return $this->hasMany(Assignment::class, 'taskID', 'taskID');
    }

    // Relationship with Workers (many-to-many via Assignment)
    public function workers() {
        return $this->belongsToMany(Worker::class, 'assignments', 'taskID', 'workerID')
                        ->withPivot(['assignDateTime'])
                        ->withTimestamps();
    }

    // Relationship with Quotations via TaskReport
    public function quotations() {
        return $this->hasManyThrough(
            Quotation::class,
            TaskReport::class,
            'taskID', // Foreign key on TaskReport table
            'quotationNo', // Foreign key on Quotation table
            'taskID', // Local key on Task table
            'quotationNo' // Local key on TaskReport table
        );
    }

    // Relationship with Invoices via TaskReport
    public function invoices() {
        return $this->hasManyThrough(
            Invoice::class,
            TaskReport::class,
            'taskID', // Foreign key on TaskReport table
            'invoiceNo', // Foreign key on Invoice table
            'taskID', // Local key on Task table
            'invoiceNo' // Local key on TaskReport table
        );
    }

    // Relationship with TaskReports
    public function taskReports() {
        return $this->hasMany(TaskReport::class, 'taskID', 'taskID');
    }
}