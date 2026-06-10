<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Services\ReservaCorreoService;
use App\Models\Sesion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ReservaController extends Controller
{
    public function index()
    {
        return response()->json(
            Reserva::with(['user', 'sesion.pelicula', 'sesion.sala', 'asientos'])
                ->orderByDesc('created_at')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'sesion_id' => 'required|exists:sesiones,id',
            'asientos' => 'required|array|min:1',
            'asientos.*' => 'exists:asientos,id',
            'nombre' => 'required|string|max:100',
            'email' => 'required|email|max:255',
        ]);

        $sesion = Sesion::with('sala')->findOrFail($request->sesion_id);

        $ocupados = DB::table('reserva_asiento')
            ->join('reservas', 'reservas.id', '=', 'reserva_asiento.reserva_id')
            ->where('reservas.sesion_id', $request->sesion_id)
            ->where('reservas.estado', '!=', 'cancelada')
            ->whereIn('reserva_asiento.asiento_id', $request->asientos)
            ->pluck('reserva_asiento.asiento_id')
            ->toArray();

        if (count($ocupados) > 0) {
            return response()->json([
                'mensaje' => 'Una o más butacas ya están ocupadas.',
                'ocupados' => $ocupados,
            ], 409);
        }

        $total = $sesion->precio * count($request->asientos);

        if (auth()->id()) {
            $userId = auth()->id();
        } else {
            $user = User::updateOrCreate(
                ['email' => $request->email],
                [
                    'name' => $request->nombre,
                    'password' => Hash::make(Str::random(32)),
                ]
            );
            $userId = $user->id;
        }

        $reserva = Reserva::create([
            'user_id' => $userId,
            'sesion_id' => $request->sesion_id,
            'estado' => 'confirmada',
            'total' => $total,
        ]);

        $asientos = [];
        foreach ($request->asientos as $asiento_id) {
            $asientos[$asiento_id] = ['precio_unitario' => $sesion->precio];
        }
        $reserva->asientos()->attach($asientos);

        $reserva->load(['asientos', 'user', 'sesion.pelicula', 'sesion.sala']);

        $emailEnviado = $this->enviarCorreoConfirmacion($reserva, $request->email);

        return response()->json([
            ...$reserva->toArray(),
            'email_enviado' => $emailEnviado,
        ], 201);
    }

    private function enviarCorreoConfirmacion(Reserva $reserva, string $email): bool
    {
        try {
            app(ReservaCorreoService::class)->enviar($reserva, $email);

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    public function show($id)
    {
        $reserva = Reserva::with([
            'user',
            'asientos',
            'sesion.pelicula',
            'sesion.sala',
        ])->findOrFail($id);

        return response()->json($reserva);
    }

    public function destroy($id)
    {
        $reserva = Reserva::findOrFail($id);
        $reserva->update(['estado' => 'cancelada']);

        return response()->json(['mensaje' => 'Reserva cancelada']);
    }
}