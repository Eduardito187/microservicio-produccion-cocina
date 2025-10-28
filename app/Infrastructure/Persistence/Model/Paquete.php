<?php

namespace App\Infrastructure\Persistence\Model;

class Paquete extends BaseModel
{
    protected $table = 'paquete';
    protected $guarded = [];

    public function etiqueta()
    {
        return $this->belongsTo(Etiqueta::class, 'etiqueta_id');
    }

    public function ventana()
    {
        return $this->belongsTo(VentanaEntrega::class, 'ventana_id');
    }

    public function direccion()
    {
        return $this->belongsTo(Direccion::class, 'direccion_id');
    }

    public function itemsDespacho()
    {
        return $this->hasMany(ItemDespacho::class, 'paquete_id');
    }
}