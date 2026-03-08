<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @class OrdenProduccion
 */
class OrdenProduccion extends BaseModel
{
    /**
     * @var mixed
     */
    protected $table = 'orden_produccion';
    /**
     * @var mixed
     */
    protected $guarded = [];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'op_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProduccionBatch::class, 'op_id');
    }

    public function despachoItems(): HasMany
    {
        return $this->hasMany(ItemDespacho::class, 'op_id');
    }
}
