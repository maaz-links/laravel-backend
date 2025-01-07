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
        Schema::table('securefile', function($table){
            $table->unsignedBigInteger('setting_id')->nullable();
            $table->foreign('setting_id')->references('id')->on('files_settings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('securefile', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['setting_id']);
        });
    }
};
