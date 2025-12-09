<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accessory_reception', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('reception_id');
            $table->unsignedBigInteger('accessory_id');

            $table->timestamps();

            $table->foreign('reception_id')->references('id')->on('receptions')->onDelete('cascade');
            $table->foreign('accessory_id')->references('id')->on('accessories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accessory_reception');
    }
};
