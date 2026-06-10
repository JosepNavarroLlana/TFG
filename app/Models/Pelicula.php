<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelicula extends Model
{
    protected $fillable = ['titulo', 'descripcion', 'duracion', 'genero', 'clasificacion', 'imagen', 'trailer_url', 'estado', 'fecha_estreno'];

    public function sesiones()
    {
        return $this->hasMany(Sesion::class);
    }
}