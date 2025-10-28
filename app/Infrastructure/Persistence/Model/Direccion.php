<?php

namespace App\Infrastructure\Persistence\Model;

class Direccion extends BaseModel
{
    protected $table = 'direccion';
    protected $guarded = [];

    protected $casts = [
        'geo' => 'array',
    ];

    public function paquetes()
    {
        return $this->hasMany(Paquete::class, 'direccion_id');
    }
}