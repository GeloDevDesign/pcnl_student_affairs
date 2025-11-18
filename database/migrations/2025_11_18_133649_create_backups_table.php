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
        Schema::create('backups', function (Blueprint $table) {
           $table->id();
            $table->string('filename');
            $table->string('path'); // full path in storage
            $table->unsignedBigInteger('size')->nullable(); // in bytes
            $table->timestamp('backed_up_at');
            $table->timestamps();

            $table->unique('filename'); // prevent duplicates
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
