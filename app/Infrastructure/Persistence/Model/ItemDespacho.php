<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @class ItemDespacho
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

    public function ordenProduccion(): BelongsTo
    {
        return $this->belongsTo(OrdenProduccion::class, 'op_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function paquete(): BelongsTo
    {
        return $this->belongsTo(Paquete::class, 'paquete_id');
    }

    public function calendarioItems(): HasMany
    {
        return $this->hasMany(CalendarioItem::class, 'item_despacho_id');
    }
}
