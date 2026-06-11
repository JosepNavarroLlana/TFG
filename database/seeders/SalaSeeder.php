<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sala;

class SalaSeeder extends Seeder
{
    public function run(): void
    {
        Sala::truncate();

        $salas = [
            ['nombre' => 'Sala 1', 'capacidad' => 70],
            ['nombre' => 'Sala 2', 'capacidad' => 70],
            ['nombre' => 'Sala 3', 'capacidad' => 70],
            ['nombre' => 'Sala 4', 'capacidad' => 70],
            ['nombre' => 'Sala 5', 'capacidad' => 70],
            ['nombre' => 'Sala 6', 'capacidad' => 70],
            ['nombre' => 'Sala 7', 'capacidad' => 70],
        ];

        foreach ($salas as $sala) {
            Sala::firstOrCreate(['nombre' => $sala['nombre']], $sala);
        }
    }
}