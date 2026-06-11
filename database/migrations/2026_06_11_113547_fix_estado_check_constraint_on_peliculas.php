<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE peliculas DROP CONSTRAINT IF EXISTS peliculas_estado_check');
        DB::statement("ALTER TABLE peliculas ADD CONSTRAINT peliculas_estado_check CHECK (estado IN ('activa', 'inactiva', 'proximamente'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE peliculas DROP CONSTRAINT IF EXISTS peliculas_estado_check');
        DB::statement("ALTER TABLE peliculas ADD CONSTRAINT peliculas_estado_check CHECK (estado IN ('activa', 'inactiva'))");
    }
};
