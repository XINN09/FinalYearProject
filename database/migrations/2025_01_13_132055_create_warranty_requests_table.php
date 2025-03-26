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
        Schema::create('warranty_requests', function (Blueprint $table) {
            $table->string('requestID', 7)->primary(); 
            $table->string('requestTitle', 30); // Renamed column
            $table->string('requesterName', 50); // New column
            $table->date('requestDate'); 
            $table->string('requestDesc', 255)->nullable(); 
            $table->enum('requestStatus', ['pending', 'accepted', 'denied'])->default('pending');
            $table->string('warrantyNo', 8);
            $table->timestamps();

            // Add foreign key constraint
            $table->foreign('warrantyNo')->references('warrantyNo')->on('warranties')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warranty_requests');
    }
};
