<?php

namespace App\Infrastructure\Persistence\Model;

class VentanaEntrega extends BaseModel
{
    protected $table = 'ventana_entrega';
    protected $guarded = [];

    protected $casts = [
        'desde' => 'datetime',
        'hasta' => 'datetime',
    ];

    public function paquetes()
    {
        return $this->hasMany(Paquete::class, 'ventana_id');
    }
}