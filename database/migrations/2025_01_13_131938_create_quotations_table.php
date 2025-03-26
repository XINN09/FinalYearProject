<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('quotations', function (Blueprint $table) {
            $table->string('quotationNo', 9)->primary();
            $table->decimal('subtotal', 9, 2);
            $table->decimal('taxRate', 3, 2);
            $table->decimal('estimatedCost', 9, 2);
            $table->decimal('previousAmount', 9, 2)->default(0.00);
            $table->decimal('balance', 9, 2)->default(0.00);
            $table->decimal('depositRate', 5, 2)->default(0.00); // New column
            $table->decimal('depositAmount', 9, 2)->default(0.00); // New column
            $table->date('validityStart');
            $table->date('validityEnd');
            $table->string('paymentInstruction', 255)->nullable();
            $table->string('reportID', 7);
            $table->timestamps();

            // Add foreign key constraint
            $table->foreign('reportID')->references('reportID')->on('reports')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('quotations');
    }
};
