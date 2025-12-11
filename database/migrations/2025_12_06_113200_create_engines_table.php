<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('engines', function (Blueprint $table) {
            $table->id();

            $table->string('hp')->nullable();          // Caballos de fuerza
            $table->string('tipo')->nullable();        // Tipo de motor
            $table->string('marca')->nullable();       // Marca
            $table->string('modelo')->nullable();      // Modelo
            $table->string('combustible')->nullable(); // Tipo de combustible
            $table->boolean(column: 'state')->default(true);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('engines');
    }
};
