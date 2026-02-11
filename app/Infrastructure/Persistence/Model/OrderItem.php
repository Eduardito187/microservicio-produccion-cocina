<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @class OrderItem
 * @package App\Infrastructure\Persistence\Model
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

    /**
     * @return BelongsTo
     */
    public function ordenProduccion(): BelongsTo
    {
        return $this->belongsTo(OrdenProduccion::class, 'op_id');
    }

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'p_id');
    }
}
