<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Worker extends Model {

    use HasFactory;

    protected $table = 'workers'; // Optional if table name matches 'workers'
    protected $primaryKey = 'workerID';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['workerID', 'availabilityStatus', 'workerType', 'userID'];

    public function user() {
        return $this->belongsTo(User::class, 'userID', 'userID');
    }

    public function contractors() {
        return $this->belongsToMany(Contractor::class, 'contractor_worker', 'workerID', 'contractorID')
                        ->withPivot('status')
                        ->withTimestamps();
    }

    public function contractorWorkers() {
        return $this->hasMany(ContractorWorker::class, 'workerID', 'workerID');
    }
}
