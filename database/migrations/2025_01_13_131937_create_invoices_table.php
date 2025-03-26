<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('invoices', function (Blueprint $table) {
            $table->string('invoiceNo', 10)->primary();
            $table->decimal('subtotal', 9, 2);
            $table->decimal('taxRate', 3, 2);
            $table->decimal('totalAmount', 9, 2);
             $table->decimal('previousAmount', 9, 2)->default(0.00);
            $table->decimal('balance', 9, 2)->default(0.00);
            $table->decimal('depositRate', 5, 2)->default(0.00); 
            $table->decimal('depositAmount', 9, 2)->default(0.00); 
            $table->date('dueDate');
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
        Schema::dropIfExists('invoices');
    }
};
