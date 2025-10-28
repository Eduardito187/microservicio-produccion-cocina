<?php

namespace App\Infrastructure\Persistence\Model;

class Suscripcion extends BaseModel
{
    protected $table = 'suscripcion';
    protected $guarded = [];

    public function pacientes()
    {
        return $this->hasMany(Paciente::class, 'suscripcion_id');
    }

    public function etiquetas()
    {
        return $this->hasMany(Etiqueta::class, 'suscripcion_id');
    }
}