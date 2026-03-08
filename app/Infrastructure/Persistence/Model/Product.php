<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @class Product
 */
class Product extends BaseModel
{
    /**
     * @var mixed
     */
    protected $table = 'products';
    /**
     * @var mixed
     */
    protected $guarded = [];

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'p_id');
    }

    public function despachoItems(): HasMany
    {
        return $this->hasMany(ItemDespacho::class, 'product_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProduccionBatch::class, 'p_id');
    }
}
