<?php

namespace App\Infrastructure\Persistence\Model;

class ItemDespacho extends BaseModel
{
    protected $table = 'item_despacho';
    protected $guarded = [];

    public function ordenProduccion()
    {
        return $this->belongsTo(OrdenProduccion::class, 'op_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function paquete()
    {
        return $this->belongsTo(Paquete::class, 'paquete_id');
    }

    public function calendarioItems()
    {
        return $this->hasMany(CalendarioItem::class, 'item_despacho_id');
    }
}