<?php

namespace App\Infrastructure\Persistence\Model;

class OrdenProduccion extends BaseModel
{
    /** Table name is Spanish, set explicitly */
    protected $table = 'orden_produccion';

    /** Allow mass assignment on everything by default (adjust as needed) */
    protected $guarded = [];

    /** Relationships */

    // One OP has many order items
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'op_id');
    }

    // One OP has many production batches
    public function batches()
    {
        return $this->hasMany(ProduccionBatch::class, 'op_id');
    }

    // One OP can have many etiquetas
    public function etiquetas()
    {
        return $this->hasMany(Etiqueta::class, 'orden_produccion_id');
    }

    // One OP can have many despacho items
    public function despachoItems()
    {
        return $this->hasMany(ItemDespacho::class, 'op_id');
    }
}