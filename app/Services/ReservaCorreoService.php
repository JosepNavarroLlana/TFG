<?php

namespace App\Services;

use App\Models\Reserva;
use Illuminate\Support\Facades\View;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;

class ReservaCorreoService
{
    public function enviar(Reserva $reserva, string $emailDestino): void
    {
        $reserva->loadMissing(['user', 'asientos', 'sesion.pelicula', 'sesion.sala']);

        $pelicula = $reserva->sesion->pelicula;
        $posterPath = $pelicula?->imagen
            ? public_path(ltrim($pelicula->imagen, '/'))
            : null;
        $tienePoster = $posterPath && file_exists($posterPath);

        $html = View::make('emails.reserva-confirmada', [
            'reserva' => $reserva,
            'tienePoster' => $tienePoster,
        ])->render();

        $titulo = $pelicula->titulo ?? 'tu película';

        $email = (new MailtrapEmail())
            ->from(new Address(
                config('mail.from.address'),
                config('mail.from.name')
            ))
            ->to(new Address($emailDestino, $reserva->user->name))
            ->subject("Confirmación de reserva — {$titulo}")
            ->html($html);

        if ($tienePoster) {
            $mime = mime_content_type($posterPath) ?: 'image/jpeg';
            $email->embed(fopen($posterPath, 'r'), 'poster', $mime);
        }

        MailtrapClient::initSendingEmails(
            apiKey: config('services.mailtrap-sdk.apiKey')
        )->send($email);
    }
}
