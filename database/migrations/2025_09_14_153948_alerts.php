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
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();

            // 🔥 En lugar de 1 solo idMovimiento, ahora un JSON con varios IDs
            $table->json('idMovimientos')->nullable();

            // Otros campos
            $table->string('descripcion')->nullable(); // No es obligatoria
            $table->date('fecha'); // Solo la fecha
            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
