<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('service_reports', function (Blueprint $table) {
            $table->string('serviceNo', 9)->primary(); // Primary key
            $table->date('serviceDate');
            $table->string('contactPerson', 30);
            $table->string('contactNo', 12);
            $table->decimal('totalAmount', 9, 2);
            $table->string('paymentInstruction', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->string('reportContent', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('service_reports');
    }
};
