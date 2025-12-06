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
        Schema::create('EmployeeMovement', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idMovimiento');
            $table->unsignedBigInteger('idEmpleado');
            $table->timestamps();

            // 🔗 Claves foráneas
            $table->foreign('idMovimiento')
                  ->references('id')->on('movimientos')
                  ->onDelete('cascade');

            $table->foreign('idEmpleado')
                  ->references('id')->on('employees')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('EmployeeMovement');
    }
};
