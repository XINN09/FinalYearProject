<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskReport extends Model {
    protected $fillable = ['taskID', 'quotationNo', 'invoiceNo'];

    // Relationship with Task
    public function task() {
        return $this->belongsTo(Task::class, 'taskID', 'taskID');
    }

    // Relationship with Quotation
    public function quotation() {
        return $this->belongsTo(Quotation::class, 'quotationNo', 'quotationNo');
    }

    // Relationship with Invoice
    public function invoice() {
        return $this->belongsTo(Invoice::class, 'invoiceNo', 'invoiceNo');
    }
}