<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('tasks', function (Blueprint $table) {
            $table->string('taskID', 7)->primary();
            $table->string('taskName', 30);
            $table->string('status', 15);
            $table->date('startDate')->nullable();
            $table->date('endDate')->nullable();
            $table->decimal('duration', 3, 1)->default(0);
            $table->string('durationUnit', 6)->default('days');
            $table->string('priority', 6)->default('None');
            $table->integer('qty')->default(0);
            $table->string('uom', 20)->nullable();
            $table->decimal('unitPrice', 10, 2)->default(0);
            $table->decimal('budget', 10, 2)->virtualAs('qty * unitPrice');
            $table->string('remarks', 100)->nullable();
            $table->string('projectID', 6);
            $table->string('warrantyNo', 8)->nullable();
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('projectID')->references('projectID')->on('projects')->onDelete('cascade');
            $table->foreign('warrantyNo')->references('warrantyNo')->on('warranties')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('tasks');
    }
};