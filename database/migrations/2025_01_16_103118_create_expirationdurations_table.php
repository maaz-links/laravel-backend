<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     
    public function up(): void
    {
        Schema::create('expirationdurations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('duration'); // Store duration in minutes
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expirationdurations');
    }
};
