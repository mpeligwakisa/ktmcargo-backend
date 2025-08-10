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
        Schema::table('users', function (Blueprint $table) {
            // Foreign key to people table
            $table->unsignedBigInteger('people_id')->nullable()->after('id');
            $table->foreign('people_id')->references('id')->on('people')->onDelete('set null');

            // Foreign key to statuses table
            $table->unsignedBigInteger('status_id')->nullable()->after('people_id');
            $table->foreign('status_id')->references('id')->on('statuses')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
             // Drop foreign keys first
             $table->dropForeign(['people_id']);
             $table->dropForeign(['status_id']);
 
             // Drop columns
             $table->dropColumn(['people_id', 'status_id', 'created_by', 'updated_by', 'deleted_at']);
        });
    }
};
