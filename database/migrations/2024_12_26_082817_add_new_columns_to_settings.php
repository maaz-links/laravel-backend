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
        Schema::table('files_settings', function (Blueprint $table) {
            $table->string('ip')->nullable();
            $table->tinyInteger('type')->default(0);
            $table->tinyInteger('block')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('files_settings', function (Blueprint $table) {
            $table->dropColumn('ip');
            $table->dropColumn('type');
            $table->dropColumn('block');
        });
    }
};
