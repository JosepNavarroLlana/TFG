<?php

namespace App\Http\Controllers;

use App\Models\Sala;
use Illuminate\Http\Request;

class SalaController extends Controller
{
    public function index()
    {
        return response()->json(Sala::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'capacidad' => 'required|integer',
        ]);

        $sala = Sala::create($request->all());
        return response()->json($sala, 201);
    }

    public function show($id)
    {
        $sala = Sala::with('asientos')->findOrFail($id);
        return response()->json($sala);
    }

    public function update(Request $request, $id)
    {
        $sala = Sala::findOrFail($id);
        $sala->update($request->all());
        return response()->json($sala);
    }

    public function destroy($id)
    {
        Sala::findOrFail($id)->delete();
        return response()->json(['mensaje' => 'Sala eliminada']);
    }
}