<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @class OrderItem
 */
class OrderItem extends BaseModel
{
    /**
     * @var mixed
     */
    protected $table = 'order_item';
    /**
     * @var mixed
     */
    protected $guarded = [];

    public function ordenProduccion(): BelongsTo
    {
        return $this->belongsTo(OrdenProduccion::class, 'op_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'p_id');
    }
}
