<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Homeowner extends Model {

    use HasFactory;

    protected $table = 'homeowners';
    protected $primaryKey = 'ownerID';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['ownerID', 'homeAddress', 'userID'];

    public function user() {
        return $this->belongsTo(User::class, 'userID', 'userID');
    }

    public function projects() {
        return $this->hasMany(Project::class, 'ownerID', 'ownerID');
    }
}
