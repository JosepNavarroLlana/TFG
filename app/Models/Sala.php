<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sala extends Model
{
    protected $fillable = ['nombre', 'capacidad'];

    public function asientos()
    {
        return $this->hasMany(Asiento::class);
    }

    public function sesiones()
    {
        return $this->hasMany(Sesion::class);
    }
}   