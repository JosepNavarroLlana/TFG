<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('peliculas', function (Blueprint $table) {
        $table->enum('estado', ['activa', 'inactiva'])->default('activa');
    });
}

public function down()
{
    Schema::table('peliculas', function (Blueprint $table) {
        $table->dropColumn('estado');
    });
}
};
