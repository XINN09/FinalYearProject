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
        Schema::create('documents', function (Blueprint $table) {
            $table->string('documentID', 7)->primary(); 
            $table->string('documentName', 30); 
            $table->string('fileType', 10); 
            $table->longText('fileContent')->nullable();
            $table->string('description', 100)->nullable(); 
            $table->string('projectID', 6); 
            $table->timestamps();

            // Add a foreign key constraint
            $table->foreign('projectID')->references('projectID')->on('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};


