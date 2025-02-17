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
        Schema::create('securefile', function (Blueprint $table) {
            $table->id();
            $table->string('file_uid');
            $table->tinyInteger('file_burn_after_read')->default(false);
            $table->string('file_detail');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('securefile');
    }
};
