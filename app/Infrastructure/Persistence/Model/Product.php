<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @package App\Infrastructure\Persistence\Model
 */
class Product extends BaseModel
{
    protected $table = 'products';
    protected $guarded = [];

    /**
     * @return HasMany
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'p_id');
    }

    /**
     * @return HasMany
     */
    public function despachoItems(): HasMany
    {
        return $this->hasMany(ItemDespacho::class, 'product_id');
    }

    /**
     * @return HasMany
     */
    public function batches(): HasMany
    {
        return $this->hasMany(ProduccionBatch::class, 'p_id');
    }
}