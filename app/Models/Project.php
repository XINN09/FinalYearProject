<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model {

    use HasFactory;

    protected $primaryKey = 'projectID';
    // If the primary key is not an incrementing integer, set this to false
    public $incrementing = false;
    // Define the primary key type if it's not an integer
    protected $keyType = 'string';
    protected $fillable = [
        'projectID', 'projectName', 'startDate', 'endDate', 'projectAddress',
        'projectDesc', 'projectStatus', 'contractorID', 'ownerID'
    ];

    public function homeowner() {
        return $this->belongsTo(Homeowner::class, 'ownerID', 'ownerID');
    }

    public function contractor() {
        return $this->belongsTo(Contractor::class, 'contractorID', 'contractorID');
    }

    public function homeownerUser() {
        return $this->hasOneThrough(User::class, Homeowner::class, 'ownerID', 'userID', 'ownerID', 'userID');
    }

    public function contractorUser() {
        return $this->hasOneThrough(User::class, Contractor::class, 'contractorID', 'userID', 'contractorID', 'userID');
    }
}
