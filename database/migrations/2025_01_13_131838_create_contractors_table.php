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
        Schema::create('contractors', function (Blueprint $table) {
            $table->string('contractorID', 7)->primary(); 
            $table->string('companyName', 40);
            $table->string('businessAddress', 100);
            $table->string('registerNo', 12); 
            $table->string('companyLogo')->nullable();
            $table->string('userID', 7); 
            $table->timestamps();

            // Add a foreign key constraint
            $table->foreign('userID')->references('userID')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contractors');
    }
};
