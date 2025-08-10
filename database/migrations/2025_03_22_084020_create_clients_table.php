<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement()->unique();
            //$table->id('user_id');
            // $table->string('fname');
            // $table->string('mname');
            // $table->string('lname');
            $table->string('name');

            $table->string('gender');
            $table->string('email');
            $table->string('phone');
            $table->string('location');
            $table->boolean('is_repeating')->default(false); // Repeating client or not
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
