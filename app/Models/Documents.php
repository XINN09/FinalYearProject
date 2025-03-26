<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documents extends Model {

    use HasFactory;

    protected $primaryKey = 'documentID'; // Set primary key
    public $incrementing = false; // Disable auto-increment
    protected $keyType = 'string'; // Use string as primary key
    protected $fillable = ['documentID', 'documentName', 'fileType', 'fileContent', 'description', 'projectID'];

    public function project() {
        return $this->belongsTo(Project::class, 'projectID', 'projectID');
    }
}
