<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractorWorker extends Model
{
    use HasFactory;

    protected $table = 'contractor_worker';

    protected $fillable = ['contractorID', 'email', 'workerID', 'dailyPay', 'status'];

    public function contractor()
    {
        return $this->belongsTo(Contractor::class, 'contractorID', 'contractorID');
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'workerID', 'workerID');
    }
}

