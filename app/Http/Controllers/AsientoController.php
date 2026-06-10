<?php

namespace App\Http\Controllers;

use App\Models\Asiento;
use Illuminate\Http\Request;

class AsientoController extends Controller
{
    public function index()
    {
        return response()->json(Asiento::with('sala')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'sala_id' => 'required|exists:salas,id',
            'fila' => 'required|string',
            'numero' => 'required|integer',
        ]);

        $asiento = Asiento::create($request->all());
        return response()->json($asiento, 201);
    }

    public function show($id)
    {
        $asiento = Asiento::with('sala')->findOrFail($id);
        return response()->json($asiento);
    }

    public function update(Request $request, $id)
    {
        $asiento = Asiento::findOrFail($id);
        $asiento->update($request->all());
        return response()->json($asiento);
    }

    public function destroy($id)
    {
        Asiento::findOrFail($id)->delete();
        return response()->json(['mensaje' => 'Asiento eliminado']);
    }
}