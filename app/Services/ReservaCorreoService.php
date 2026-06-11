<?php

namespace App\Services;

use App\Models\Reserva;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;

class ReservaCorreoService
{
    public function enviar(Reserva $reserva, string $emailDestino): void
    {
        $reserva->loadMissing([
            'user',
            'asientos',
            'sesion.pelicula',
            'sesion.sala',
        ]);

        $pelicula = $reserva->sesion->pelicula;
        $titulo = $pelicula->titulo ?? 'tu película';

        $html = View::make('emails.reserva-confirmada', [
            'reserva' => $reserva,
            'tienePoster' => false,
        ])->render();

        Mail::html($html, function ($message) use ($emailDestino, $titulo, $reserva) {
            $message
                ->to($emailDestino, optional($reserva->user)->name ?? 'Cliente')
                ->subject("Confirmación de reserva — {$titulo}");
        });
    }
}