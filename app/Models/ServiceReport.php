<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceReport extends Model {

    protected $primaryKey = 'serviceNo';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'serviceNo',
        'serviceDate',
        'contactPerson',
        'contactNo',
        'totalAmount',
        'paymentInstruction',
        'remarks',
    ];

    // Define the one-to-many relationship with Issue
    public function issue() {
        return $this->hasOne(Issues::class, 'serviceNo', 'serviceNo');
    }
}
