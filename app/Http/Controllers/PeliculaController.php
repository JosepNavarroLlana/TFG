<?php

namespace App\Http\Controllers;

use App\Models\Pelicula;
use Illuminate\Http\Request;

class PeliculaController extends Controller
{
    public function index()
    {
        return response()->json(
            Pelicula::where('estado', 'activa')->orderBy('titulo')->get()
        );
    }

    public function todas()
    {
        return response()->json(
            Pelicula::orderBy('titulo')->get()
        );
    }

    public function proximamente()
    {
        return response()->json(
            Pelicula::where('estado', 'proximamente')
                ->orderBy('fecha_estreno')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string',
            'descripcion' => 'required|string',
            'duracion' => 'required|integer',
            'genero' => 'required|string',
            'clasificacion' => 'required|string',
        ]);

        $pelicula = Pelicula::create($request->all());
        return response()->json($pelicula, 201);
    }

    public function show($id)
    {
        $pelicula = Pelicula::with(['sesiones' => function ($query) {
            $query->with('sala')->orderBy('fecha_hora');
        }])->findOrFail($id);

        return response()->json($pelicula);
    }

    public function subirImagen(Request $request)
    {
        $request->validate(['imagen' => 'required|image|max:4096']);

        $archivo = $request->file('imagen');
        $nombre = \Illuminate\Support\Str::slug(
            pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME)
        ) . '-' . time() . '.' . $archivo->getClientOriginalExtension();

        $archivo->move(public_path('images/peliculas'), $nombre);

        return response()->json(['ruta' => '/images/peliculas/' . $nombre]);
    }

    public function update(Request $request, $id)
    {
        $pelicula = Pelicula::findOrFail($id);
        $pelicula->update($request->all());
        return response()->json($pelicula);
    }

    public function destroy($id)
    {
        Pelicula::findOrFail($id)->delete();
        return response()->json(['mensaje' => 'Película eliminada']);
    }
}