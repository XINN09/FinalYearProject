<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    // Specify the table name (optional if table name is 'assignments')
    protected $table = 'assignments';

    // Define the primary key
    protected $primaryKey = 'assignmentID';

    // Disable auto-increment since assignmentID is a string
    public $incrementing = false;

    // Specify the key type as string
    protected $keyType = 'string';

    // Define the fillable attributes for mass assignment
    protected $fillable = [
        'assignmentID',
        'assignDateTime',
        'taskID',
        'workerID',
        'contractorID',
    ];

    // Automatically generate assignmentID when creating a new record
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($assignment) {
            // Generate the assignmentID based on the format A<YY><NNNN>
            $year = now()->format('y'); // Get the current year in YY format
            $lastAssignment = self::where('assignmentID', 'like', "A{$year}%")
                ->orderBy('assignmentID', 'desc')
                ->first();

            $lastNumber = $lastAssignment ? intval(substr($lastAssignment->assignmentID, 3)) : 0;
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

            $assignment->assignmentID = "A{$year}{$newNumber}";
        });
    }

    // Define relationships with other models
    public function task()
    {
        return $this->belongsTo(Task::class, 'taskID', 'taskID');
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'workerID', 'workerID');
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class, 'contractorID', 'contractorID');
    }
}
