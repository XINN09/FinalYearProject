<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->string('projectID', 6)->primary(); 
            $table->string('projectName', 40); 
            $table->date('startDate');
            $table->date('endDate')->nullable();
            $table->string('projectAddress', 100); 
            $table->string('projectDesc', 100)->nullable(); 
            $table->string('projectStatus', 20)->default('Active'); 
            $table->string('contractorID', 7); 
            $table->string('ownerID', 7)->nullable();
            $table->timestamps();

            // Add foreign key constraints
            $table->foreign('contractorID')->references('contractorID')->on('contractors')->onDelete('cascade');
            $table->foreign('ownerID')->references('ownerID')->on('homeowners');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
