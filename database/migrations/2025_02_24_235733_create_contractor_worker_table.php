<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up() {
        Schema::create('contractor_worker', function (Blueprint $table) {
            $table->id();
            $table->string('contractorID', 7);
            $table->string('email')->unique(); 
            $table->string('workerID', 7)->nullable(); 
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending'); 
            $table->decimal('dailyPay', 7, 2)->nullable();
            $table->timestamps();

            $table->foreign('contractorID')->references('contractorID')->on('contractors')->onDelete('cascade');
            $table->foreign('workerID')->references('workerID')->on('workers')->onDelete('set null'); // Worker might be missing initially
        });
    }

    public function down() {
        Schema::dropIfExists('contractor_worker');
    }
};
