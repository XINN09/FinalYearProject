<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('warranties', function (Blueprint $table) {
            $table->string('warrantyNo', 8)->primary();
            $table->date('startDate');
            $table->date('endDate');
            $table->integer('duration');
            $table->string('durationUnit', 6);
            $table->string('status', 20);
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('warranties');
    }
};
