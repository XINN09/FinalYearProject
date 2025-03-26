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
        Schema::create('task_reports', function (Blueprint $table) {
            $table->id();
            $table->string('taskID', 7); 
            $table->string('quotationNo', 9)->nullable();
            $table->string('invoiceNo', 10)->nullable(); 
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('taskID')->references('taskID')->on('tasks')->onDelete('cascade');
            $table->foreign('quotationNo')->references('quotationNo')->on('quotations')->onDelete('set null');
            $table->foreign('invoiceNo')->references('invoiceNo')->on('invoices')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_reports');
    }
};