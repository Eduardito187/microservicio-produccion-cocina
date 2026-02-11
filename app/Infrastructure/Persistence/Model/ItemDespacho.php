<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @class ItemDespacho
 * @package App\Infrastructure\Persistence\Model
 */
class ItemDespacho extends BaseModel
{
    /**
     * @var mixed
     */
    protected $table = 'item_despacho';
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
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * @return BelongsTo
     */
    public function paquete(): BelongsTo
    {
        return $this->belongsTo(Paquete::class, 'paquete_id');
    }

    /**
     * @return HasMany
     */
    public function calendarioItems(): HasMany
    {
        return $this->hasMany(CalendarioItem::class, 'item_despacho_id');
    }
}
