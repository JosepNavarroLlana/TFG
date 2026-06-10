<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('cine:generar-sesiones {dias=7}', function (int $dias) {
    // Cada película conserva su horario habitual: se toman las combinaciones
    // sala + hora + precio ya existentes y se replican en los próximos días.
    $plantillas = \App\Models\Sesion::all()
        ->groupBy('pelicula_id')
        ->map(fn ($sesiones) => $sesiones
            ->map(fn ($s) => [
                'sala_id' => $s->sala_id,
                'hora' => \Illuminate\Support\Carbon::parse($s->fecha_hora)->format('H:i:s'),
                'precio' => $s->precio,
            ])
            ->unique(fn ($t) => $t['sala_id'].'|'.$t['hora'])
            ->values());

    if ($plantillas->isEmpty()) {
        $this->error('No hay sesiones en la base de datos que sirvan de plantilla.');

        return 1;
    }

    $creadas = 0;

    foreach ($plantillas as $peliculaId => $horarios) {
        for ($offset = 0; $offset < $dias; $offset++) {
            $dia = today()->addDays($offset)->format('Y-m-d');

            foreach ($horarios as $horario) {
                $fechaHora = $dia.' '.$horario['hora'];

                $existe = \App\Models\Sesion::where('pelicula_id', $peliculaId)
                    ->where('sala_id', $horario['sala_id'])
                    ->where('fecha_hora', $fechaHora)
                    ->exists();

                if (! $existe) {
                    \App\Models\Sesion::create([
                        'pelicula_id' => $peliculaId,
                        'sala_id' => $horario['sala_id'],
                        'fecha_hora' => $fechaHora,
                        'precio' => $horario['precio'],
                    ]);
                    $creadas++;
                }
            }
        }
    }

    $this->info("Creadas {$creadas} sesiones nuevas (desde hoy hasta dentro de {$dias} días).");

    return 0;
})->purpose('Generar sesiones para los próximos días a partir de los horarios existentes');

Artisan::command('send-mail', function () {
    $apiKey = config('services.mailtrap-sdk.apiKey');

    if (empty($apiKey)) {
        $this->error('Falta MAILTRAP_API_KEY en el .env');

        return 1;
    }

    $email = (new MailtrapEmail())
        ->from(new Address(
            config('mail.from.address'),
            config('mail.from.name')
        ))
        ->to(new Address('jnavarrollana@gmail.com'))
        ->subject('Prueba Cine — Mailtrap')
        ->category('Integration Test')
        ->text('Si lees esto, el envío real con Mailtrap funciona.');

    $response = MailtrapClient::initSendingEmails(
        apiKey: $apiKey
    )->send($email);

    $this->info('Correo enviado. Revisa tu bandeja (y spam).');
    $this->line(json_encode(ResponseHelper::toArray($response)));

    return 0;
})->purpose('Enviar correo de prueba con Mailtrap API');
