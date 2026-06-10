<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE peliculas MODIFY estado ENUM('activa', 'inactiva', 'proximamente') NOT NULL DEFAULT 'activa'");

        Schema::table('peliculas', function (Blueprint $table) {
            $table->date('fecha_estreno')->nullable()->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('peliculas', function (Blueprint $table) {
            $table->dropColumn('fecha_estreno');
        });

        DB::statement("ALTER TABLE peliculas MODIFY estado ENUM('activa', 'inactiva') NOT NULL DEFAULT 'activa'");
    }
};
