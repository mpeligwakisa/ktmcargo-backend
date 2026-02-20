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
        Schema::table('cargos', function (Blueprint $table) {
            $table->decimal('weight/cbm', 10, 2)->nullable();
            // if (!Schema::hasColumn('cargos', 'weight/cbm')) {
            //     $table->decimal('weight/cbm', 10, 2)->nullable();
            // }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cargos', function (Blueprint $table) {
            //
        });
    }
};
