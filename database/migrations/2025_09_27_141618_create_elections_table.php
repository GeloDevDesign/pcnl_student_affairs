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
        Schema::create('elections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            
            // Start fields (Order matters here: start_time will be created after start_date)
            $table->dateTime('start_date');
            $table->time('start_time')->nullable(); 

            // End fields
            $table->dateTime('end_date');
            $table->time('end_time')->nullable();

            $table->tinyInteger('status')->nullable()->default(0);
            $table->boolean('is_set')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('elections');
    }
};
