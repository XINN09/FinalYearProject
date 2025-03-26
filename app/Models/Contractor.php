<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contractor extends Model {

    use HasFactory;
    

    // Define the primary key
    protected $primaryKey = 'contractorID';
    // Disable auto-incrementing since it's not an integer
    public $incrementing = false;
    // Specify the key type
    protected $keyType = 'string';
    // Allow mass assignment
    protected $fillable = [
        'contractorID',
        'companyName',
        'businessAddress',
        'registerNo',
        'companyLogo',
        'userID',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'userID');
    }

    public function projects() {
        return $this->hasMany(Project::class, 'contractorID', 'contractorID');
    }

    public function workers() {
        return $this->hasMany(ContractorWorker::class, 'contractorID', 'contractorID');
    }
}
