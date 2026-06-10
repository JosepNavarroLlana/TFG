<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $fillable = ['user_id', 'sesion_id', 'estado', 'total'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sesion()
    {
        return $this->belongsTo(Sesion::class);
    }

    public function asientos()
    {
        return $this->belongsToMany(Asiento::class, 'reserva_asiento')
                    ->withPivot('precio_unitario');
    }
}