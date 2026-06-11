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
        $filas = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];

        foreach ($salas as $sala) {
            foreach ($filas as $fila) {
                for ($i = 1; $i <= 10; $i++) {
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