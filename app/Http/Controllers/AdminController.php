<?php

namespace App\Http\Controllers;

use App\Models\Sala;
use App\Models\Sesion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['mensaje' => 'Credenciales incorrectas.'], 401);
        }

        $token = $request->user()->createToken('admin')->plainTextToken;

        return response()->json(['token' => $token]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['mensaje' => 'Sesión cerrada.']);
    }

    public function ocupacion()
    {
        $salas = Sala::with(['asientos'])->get();

        $sesionesHoy = Sesion::with(['pelicula', 'sala'])
            ->whereDate('fecha_hora', today())
            ->orderBy('fecha_hora')
            ->get();

        $result = $salas->map(function ($sala) use ($sesionesHoy) {
            $total = $sala->asientos->count();

            $sesiones = $sesionesHoy
                ->where('sala_id', $sala->id)
                ->values()
                ->map(function ($sesion) use ($total) {
                    $ocupados = DB::table('reserva_asiento')
                        ->join('reservas', 'reservas.id', '=', 'reserva_asiento.reserva_id')
                        ->where('reservas.sesion_id', $sesion->id)
                        ->where('reservas.estado', '!=', 'cancelada')
                        ->count();

                    return [
                        'id' => $sesion->id,
                        'pelicula' => $sesion->pelicula?->titulo,
                        'fecha_hora' => $sesion->fecha_hora,
                        'ocupados' => $ocupados,
                        'total' => $total,
                        'porcentaje' => $total > 0 ? round($ocupados / $total * 100) : 0,
                    ];
                });

            return [
                'id' => $sala->id,
                'nombre' => $sala->nombre,
                'capacidad' => $total,
                'sesiones' => $sesiones,
            ];
        });

        return response()->json($result);
    }
}
