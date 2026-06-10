<?php

namespace App\Http\Controllers;

use App\Models\Asiento;
use App\Models\Sesion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SesionController extends Controller
{
    public function index()
    {
        return response()->json(Sesion::with(['pelicula', 'sala'])->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'pelicula_id' => 'required|exists:peliculas,id',
            'sala_id' => 'required|exists:salas,id',
            'fecha_hora' => 'required|date',
            'precio' => 'required|numeric',
        ]);

        $sesion = Sesion::create($request->all());
        return response()->json($sesion, 201);
    }

    public function hoy()
    {
        $sesiones = Sesion::with(['pelicula', 'sala'])
            ->whereDate('fecha_hora', today())
            ->where('fecha_hora', '>', now())
            ->whereHas('pelicula', fn ($q) => $q->where('estado', 'activa'))
            ->orderBy('fecha_hora')
            ->get();

        return response()->json($sesiones);
    }

    public function show($id)
    {
        $sesion = Sesion::with(['pelicula', 'sala', 'reservas'])->findOrFail($id);
        return response()->json($sesion);
    }

    public function asientos($id)
    {
        $sesion = Sesion::with('sala')->findOrFail($id);

        $asientos = Asiento::where('sala_id', $sesion->sala_id)
            ->orderBy('fila')
            ->orderBy('numero')
            ->get();

        $ocupados = DB::table('reserva_asiento')
            ->join('reservas', 'reservas.id', '=', 'reserva_asiento.reserva_id')
            ->where('reservas.sesion_id', $id)
            ->where('reservas.estado', '!=', 'cancelada')
            ->pluck('reserva_asiento.asiento_id')
            ->toArray();

        return response()->json([
            'sesion' => $sesion,
            'sala' => $sesion->sala,
            'asientos' => $asientos,
            'ocupados' => $ocupados,
        ]);
    }

    public function update(Request $request, $id)
    {
        $sesion = Sesion::findOrFail($id);
        $sesion->update($request->all());
        return response()->json($sesion);
    }

    public function destroy($id)
    {
        Sesion::findOrFail($id)->delete();
        return response()->json(['mensaje' => 'Sesión eliminada']);
    }
}