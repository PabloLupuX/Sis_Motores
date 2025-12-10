<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('receptions', function (Blueprint $table) {
            $table->id();

            // Motor recibido
            $table->unsignedBigInteger('engine_id');

            // Cliente dueño del motor
            $table->unsignedBigInteger('customer_owner_id');

            // Cliente que entrega/recoge
            $table->unsignedBigInteger('customer_contact_id');
            // Número de serie del motor o equipo
            $table->string('numero_serie')->nullable();
            // Descripción del problema
            $table->text('problema')->nullable();

            // Fechas de proceso
            $table->dateTime('fecha_ingreso')->nullable();
            $table->dateTime('fecha_resuelto')->nullable();
            $table->dateTime('fecha_entrega')->nullable();

            // Estado
            $table->boolean('state')->default(true);

            $table->timestamps();

            // Relaciones
            $table->foreign('engine_id')->references('id')->on('engines');
            $table->foreign('customer_owner_id')->references('id')->on('customers');
            $table->foreign('customer_contact_id')->references('id')->on('customers');
        });
    }

    public function down()
    {
        Schema::dropIfExists('receptions');
    }
};
