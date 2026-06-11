<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Cambiar el tipo de la columna estado a VARCHAR(20) compatible con PostgreSQL
        DB::statement("ALTER TABLE peliculas ALTER COLUMN estado TYPE VARCHAR(20)");
        DB::statement("ALTER TABLE peliculas ALTER COLUMN estado SET DEFAULT 'activa'");
        DB::statement("ALTER TABLE peliculas ALTER COLUMN estado SET NOT NULL");

        Schema::table('peliculas', function (Blueprint $table) {
            // ->after() es solo MySQL, en PostgreSQL se ignora (columna va al final)
            $table->date('fecha_estreno')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('peliculas', function (Blueprint $table) {
            $table->dropColumn('fecha_estreno');
        });

        // Revertir a VARCHAR(20) sin valor proximamente (no se puede revertir ENUM en PG fácilmente)
        DB::statement("ALTER TABLE peliculas ALTER COLUMN estado TYPE VARCHAR(20)");
        DB::statement("ALTER TABLE peliculas ALTER COLUMN estado SET DEFAULT 'activa'");
    }
};
