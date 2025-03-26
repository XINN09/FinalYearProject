<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('assignments', function (Blueprint $table) {
            $table->string('assignmentID', 7)->primary();
            $table->timestamp('assignDateTime');
            $table->string('taskID', 7);
            $table->string('workerID', 7)->nullable();  // Allow null since it might be a contractor
            $table->string('contractorID', 7)->nullable();  // Add contractorID
            $table->timestamps();

            // Add foreign key constraints
            $table->foreign('taskID')->references('taskID')->on('tasks')->onDelete('cascade');
            $table->foreign('workerID')->references('workerID')->on('workers')->onDelete('cascade');
            $table->foreign('contractorID')->references('contractorID')->on('contractors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('assignments');
    }
};
