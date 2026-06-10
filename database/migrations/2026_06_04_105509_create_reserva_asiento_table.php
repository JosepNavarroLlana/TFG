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
    Schema::create('reserva_asiento', function (Blueprint $table) {
        $table->id();
        $table->foreignId('reserva_id')->constrained()->onDelete('cascade');
        $table->foreignId('asiento_id')->constrained()->onDelete('cascade');
        $table->decimal('precio_unitario', 5, 2);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reserva_asiento');
    }
};
