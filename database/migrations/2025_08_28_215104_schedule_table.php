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
        Schema::create('schedule_table', function (Blueprint $table) {
            $table->id();
            $table->date('fecha'); // fecha general
            $table->dateTime('fechaInicio'); // fecha y hora de inicio
            $table->dateTime('fechaFin');    // fecha y hora de fin
            // Relaciones
            $table->foreignId('idEspacio')->constrained('spaces','id');
            $table->foreignId('idEmpleado')->constrained('employees','id');
            $table->boolean('state')->default(true)->comment('true: activo, false: inactivo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_table');
    }
};
