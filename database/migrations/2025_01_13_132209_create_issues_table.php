<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('issues', function (Blueprint $table) {
            $table->string('issuesID', 7)->primary(); // Primary key
            $table->string('issuesName', 30);
            $table->string('issueHandler', 50)->nullable();
            $table->string('issuesStatus', 20);
            $table->string('severity', 10)->nullable();
            $table->decimal('budget', 7, 2)->nullable();
            $table->date('dueDate')->nullable();
            $table->string('requestID', 7); // Foreign key to warranty_requests
            $table->string('serviceNo', 9)->nullable(); // Foreign key to service_reports
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('requestID')->references('requestID')->on('warranty_requests')->onDelete('cascade');
            $table->foreign('serviceNo')->references('serviceNo')->on('service_reports')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('issues');
    }
};
