<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $primaryKey = 'paymentID';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'paymentID',
        'paymentDate',
        'paymentType',
        'paymentStatus',
        'paymentAmount',
        'receipt',
        'remarks',
        'invoiceNo',
        'quotationNo',
        'serviceNo'
    ];

    // Define relationships
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoiceNo', 'invoiceNo');
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotationNo', 'quotationNo');
    }

    public function serviceReport()
    {
        return $this->belongsTo(ServiceReport::class, 'serviceNo', 'serviceNo');
    }
}
