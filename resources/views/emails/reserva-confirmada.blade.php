@php
    $pelicula = $reserva->sesion->pelicula;
    $sesion = $reserva->sesion;
    $sala = $sesion->sala;
    $fecha = \Carbon\Carbon::parse($sesion->fecha_hora);
    $asientos = $reserva->asientos->sortBy(['fila', 'numero']);
    $tienePoster = $tienePoster ?? false;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmación de reserva</title>
</head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;color:#222;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f5f5f5;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background:#ffffff;max-width:600px;width:100%;">
                    <tr>
                        <td style="padding:28px 32px 8px;">
                            <p style="margin:0 0 6px;font-size:13px;color:#666;">Reserva confirmada · Nº {{ $reserva->id }}</p>
                            <p style="margin:0;font-size:13px;color:#666;">Hola {{ $reserva->user->name }}, aquí tienes los detalles de tu compra.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px 28px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    @if($tienePoster)
                                    <td width="120" valign="top" style="padding-right:20px;">
                                        <img
                                            src="cid:poster"
                                            alt="{{ $pelicula->titulo }}"
                                            width="110"
                                            style="display:block;width:110px;height:auto;border:0;"
                                        />
                                    </td>
                                    @endif
                                    <td valign="top">
                                        <p style="margin:0 0 10px;font-size:18px;font-weight:bold;letter-spacing:0.04em;text-transform:uppercase;">
                                            {{ $pelicula->titulo }}
                                        </p>
                                        <p style="margin:0 0 18px;font-size:14px;color:#333;">
                                            {{ $fecha->format('d/m/Y') }} - {{ $fecha->format('H:i') }} - {{ $sala->nombre }}
                                        </p>

                                        <p style="margin:0 0 4px;font-size:14px;font-weight:bold;">{{ config('cine.nombre') }}</p>
                                        <p style="margin:0 0 2px;font-size:13px;color:#444;">{{ config('cine.razon_social') }} - CIF: {{ config('cine.cif') }}</p>
                                        <p style="margin:0 0 2px;font-size:13px;color:#444;">{{ config('cine.ciudad') }}</p>
                                        <p style="margin:0 0 18px;font-size:13px;color:#444;">{{ config('cine.direccion') }}</p>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:8px;">
                                @foreach($asientos as $asiento)
                                <tr>
                                    <td style="padding:10px 0 2px;font-size:14px;color:#333;">
                                        Fila: {{ $asiento->fila }}, Butaca: {{ $asiento->numero }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 0 12px;font-size:14px;font-weight:bold;color:#111;">
                                        {{ number_format($asiento->pivot->precio_unitario, 2, ',', '.') }} Euros.
                                    </td>
                                </tr>
                                @endforeach
                            </table>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:16px;">
                                <tr>
                                    <td align="right" style="font-size:15px;font-weight:bold;color:#111;">
                                        Total Compra: {{ number_format($reserva->total, 2, ',', '.') }} Euros
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 32px 28px;">
                            <p style="margin:0;font-size:12px;color:#888;line-height:1.5;">
                                Presenta el número de reserva en taquilla. Este correo es una confirmación de tu compra.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
