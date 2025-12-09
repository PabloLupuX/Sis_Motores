<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reception_media', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('reception_id');

            $table->enum('type', ['foto', 'video', 'audio']);
            $table->string('url'); // URL del archivo en hosting/API

            $table->timestamps();

            $table->foreign('reception_id')->references('id')->on('receptions')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reception_media');
    }
};
