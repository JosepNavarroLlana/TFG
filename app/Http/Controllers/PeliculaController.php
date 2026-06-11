<?php

namespace App\Http\Controllers;

use App\Models\Pelicula;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        if ($this->cloudinaryConfigurado()) {
            return response()->json([
                'ruta' => $this->subirImagenACloudinary($request),
            ]);
        }

        $archivo = $request->file('imagen');
        $nombre = Str::slug(
            pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME)
        ) . '-' . time() . '.' . $archivo->getClientOriginalExtension();

        Storage::disk('public')->putFileAs('peliculas', $archivo, $nombre);

        return response()->json(['ruta' => Storage::url('peliculas/' . $nombre)]);
    }

    private function cloudinaryConfigurado(): bool
    {
        return filled(config('services.cloudinary.cloud_name'))
            && filled(config('services.cloudinary.api_key'))
            && filled(config('services.cloudinary.api_secret'));
    }

    private function subirImagenACloudinary(Request $request): string
    {
        $cloudName = config('services.cloudinary.cloud_name');
        $apiKey = config('services.cloudinary.api_key');
        $apiSecret = config('services.cloudinary.api_secret');
        $folder = config('services.cloudinary.folder');
        $timestamp = time();

        $params = [
            'folder' => $folder,
            'timestamp' => $timestamp,
        ];

        $signature = sha1(
            collect($params)
                ->sortKeys()
                ->map(fn ($value, $key) => "{$key}={$value}")
                ->implode('&') . $apiSecret
        );

        $response = Http::attach(
            'file',
            file_get_contents($request->file('imagen')->getRealPath()),
            $request->file('imagen')->getClientOriginalName()
        )->post("https://api.cloudinary.com/v1_1/{$cloudName}/image/upload", [
            'api_key' => $apiKey,
            'folder' => $folder,
            'timestamp' => $timestamp,
            'signature' => $signature,
        ]);

        $response->throw();

        return $response->json('secure_url');
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
