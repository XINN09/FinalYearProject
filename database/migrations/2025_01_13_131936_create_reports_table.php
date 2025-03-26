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
        Schema::create('reports', function (Blueprint $table) {
            $table->string('reportID', 7)->primary(); 
            $table->date('reportDate'); 
            $table->string('remarks', 100)->nullable(); 
            $table->string('projectID', 6); 
            $table->timestamps();

            // Add foreign key constraint
            $table->foreign('projectID')->references('projectID')->on('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
