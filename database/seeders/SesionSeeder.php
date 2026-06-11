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
        Sesion::truncate();

        $peliculas = Pelicula::where('estado', 'activa')->get();
        $salas = Sala::all();

        if ($peliculas->isEmpty() || $salas->isEmpty()) {
            $this->command->info('No hay películas activas o salas. Skipping.');
            return;
        }

        $horarios = ['18:30', '19:30', '20:30', '21:30', '22:30'];
        $precio = 5.00;

        for ($dia = 0; $dia < 7; $dia++) {
            $fecha = Carbon::now()->addDays($dia)->format('Y-m-d');

            foreach ($horarios as $hora) {
                $fechaHora = $fecha . ' ' . $hora;
                $peliculasDisponibles = $peliculas->shuffle();
                $salaIndex = 0;

                foreach ($peliculasDisponibles as $pelicula) {
                    if ($salaIndex >= $salas->count())
                        break;

                    $sala = $salas[$salaIndex];

                    Sesion::firstOrCreate(
                        [
                            'sala_id' => $sala->id,
                            'fecha_hora' => $fechaHora,
                        ],
                        [
                            'pelicula_id' => $pelicula->id,
                            'precio' => $precio,
                        ]
                    );

                    $salaIndex++;
                }
            }
        }
    }
}