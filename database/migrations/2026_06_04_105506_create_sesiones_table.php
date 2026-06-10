<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('sesiones', function (Blueprint $table) {
        $table->id();
        $table->foreignId('pelicula_id')->constrained()->onDelete('cascade');
        $table->foreignId('sala_id')->constrained()->onDelete('cascade');
        $table->dateTime('fecha_hora');
        $table->decimal('precio', 5, 2);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesiones');
    }
};
