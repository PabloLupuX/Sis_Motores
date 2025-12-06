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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('apellido');
            $table->string('codigo', 8)->unique(); // <--- CAMBIO: string(8) en lugar de integer
            $table->foreignId('employee_type_id')->constrained('employee_types','id');
            $table->integer('idHuella')->unique(); // entero, no FK
            $table->string('foto');      // ruta de foto en storage
            $table->boolean('state')->default(true)->comment('true: activo, false: inactivo');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};