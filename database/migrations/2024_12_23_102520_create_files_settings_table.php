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
        Schema::create('files_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('expiry_date')->nullable();
            $table->tinyInteger('burn_after_read')->default(false);
            $table->string('uid')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files_settings');
    }
};
