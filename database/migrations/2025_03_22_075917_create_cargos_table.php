<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCargosTable extends Migration
{
    /**
     * Run the migrations.
     * 
     * @return void
     */
    public function up()
    {
        Schema::create('cargos', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->unsignedBigInteger('client_id');
            $table->string('cargo_number');
            $table->string('tracking_number');
           // $table->enum('transport_mode', ['AIR', 'SEA']);
            $table->foreignId('transport_id')->constrained('transports')->onDelete('cascade');
            $table->enum('measure_unit', ['WEIGHT', 'CBM']);
            $table->decimal('measure_value', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2);
            $table->decimal('pending_amount', 10, 2);
            $table->enum('payment_status', ['PAID', 'PARTIAL', 'UNPAID']);
            $table->string('location');
            $table->timestamps();
            $table->softDeletes();

            // Add foreign key constraint for client_id
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     * 
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('cargos');
    }
};
