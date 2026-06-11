<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sala;
use App\Models\Asiento;

class AsientoSeeder extends Seeder
{
    public function run(): void
    {
        $salas = Sala::all();

        foreach ($salas as $sala) {
            if ($sala->capacidad === 75) {
                $filas = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
                $asientosPorFila = 10;
                $extra = 5;
            } else {
                $filas = ['A', 'B', 'C', 'D', 'E'];
                $asientosPorFila = 10;
                $extra = 0;
            }

            foreach ($filas as $fila) {
                $cantidad = $asientosPorFila;
                if ($extra > 0 && $fila === 'G') {
                    $cantidad = $extra;
                }
                for ($i = 1; $i <= $cantidad; $i++) {
                    Asiento::firstOrCreate([
                        'sala_id' => $sala->id,
                        'fila' => $fila,
                        'numero' => $i,
                    ]);
                }
            }
        }
    }
}