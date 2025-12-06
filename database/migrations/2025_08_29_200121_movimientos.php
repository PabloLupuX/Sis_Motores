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
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();
            
            // Relación con spaces
            $table->foreignId('idEspacio')->constrained('spaces','id');

            // Tipo de reconocimiento (1 = Cara, 2 = Huella)
            $table->tinyInteger('idTipo')->comment('1: Cara, 2: Huella');

            $table->boolean('reconocido')->default(false)->comment('0: No reconocido, 1: Reconocido');
            $table->boolean('access')->default(false)->comment('0: Sin acceso, 1: Con acceso');
            $table->string('error', 3)->nullable();

            $table->dateTime('fechaEnvioESP32')->nullable();
            $table->dateTime('fechaRecepcion')->nullable();
            $table->dateTime('fechaReconocimiento')->nullable();

            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};
