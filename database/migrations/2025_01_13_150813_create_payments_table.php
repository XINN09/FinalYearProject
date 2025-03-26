<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('payments', function (Blueprint $table) {
            $table->string('paymentID', 7)->primary();
            $table->date('paymentDate')->nullable();
            $table->string('paymentType', 20);
            $table->string('paymentStatus', 20);
            $table->decimal('paymentAmount', 12, 2); 
            $table->string('receipt')->nullable();
            $table->text('remarks')->nullable();
            $table->string('invoiceNo', 10)->nullable();
            $table->string('quotationNo', 9)->nullable();
            $table->string('serviceNo', 9)->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('invoiceNo')->references('invoiceNo')->on('invoices')->onDelete('cascade');
            $table->foreign('quotationNo')->references('quotationNo')->on('quotations')->onDelete('cascade');
            $table->foreign('serviceNo')->references('serviceNo')->on('service_reports')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('payments');
    }
};
