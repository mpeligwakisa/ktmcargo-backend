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
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('cargo_name');
            $table->string('cargo_number')->unique();
            $table->string('container_number');
            $table->string('tracking_number')->unique();
            $table->string('category')->nullable();
            $table->integer('quantity');
            $table->foreignId('measurement_id')->constrained('measurements')->onDelete('cascade');
            $table->decimal('value', 15, 2)->nullable();
            $table->foreignId('origin_location_id')->constrained('locations')->onDelete('cascade');
            $table->foreignId('destination_location_id')->constrained('locations')->onDelete('cascade');
            $table->foreignId('transport_id')->constrained('transports')->onDelete('cascade');
            $table->string('packaging')->nullable();
            $table->enum('status')->nullable();
            $table->text('special_instructions')->nullable();
            $table->date('eta')->nullable();
            $table->foreignId('created_by')->constrained('users', 'id')->onDelete('set null');
            $table->foreignId('updated_by')->constrained('users', 'id')->onDelete('set null');
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
}
;
