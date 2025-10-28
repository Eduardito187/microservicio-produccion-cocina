<?php

namespace App\Infrastructure\Persistence\Model;

class Paciente extends BaseModel
{
    protected $table = 'paciente';
    protected $guarded = [];

    public function suscripcion()
    {
        return $this->belongsTo(Suscripcion::class, 'suscripcion_id');
    }

    public function etiquetas()
    {
        return $this->hasMany(Etiqueta::class, 'paciente_id');
    }
}