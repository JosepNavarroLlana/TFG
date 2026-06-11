<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pelicula;
use App\Models\Sala;
use App\Models\Sesion;
use Carbon\Carbon;

class SesionSeeder extends Seeder
{
    public function run(): void
    {
        $peliculas = Pelicula::where('estado', 'activa')->get();
        $salas = Sala::all();

        if ($peliculas->isEmpty() || $salas->isEmpty()) {
            $this->command->info('No hay películas activas o salas. Skipping.');
            return;
        }

        $horarios = ['18:30', '19:30', '20:30', '21:30', '22:30'];
        $precio = 5.00;

        $salaIndex = 0;

        foreach ($peliculas as $pelicula) {
            for ($dia = 0; $dia < 7; $dia++) {
                foreach ($horarios as $hora) {
                    $fecha = Carbon::now()->addDays($dia)->format('Y-m-d') . ' ' . $hora;
                    $sala = $salas[$salaIndex % $salas->count()];

                    Sesion::firstOrCreate(
                        [
                            'pelicula_id' => $pelicula->id,
                            'sala_id' => $sala->id,
                            'fecha_hora' => $fecha,
                        ],
                        ['precio' => $precio]
                    );

                    $salaIndex++;
                }
            }
        }
    }
}