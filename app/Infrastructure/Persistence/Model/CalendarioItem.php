<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @class CalendarioItem
 */
class CalendarioItem extends BaseModel
{
    /**
     * @var mixed
     */
    protected $table = 'calendario_item';
    /**
     * @var mixed
     */
    protected $guarded = [];

    public function calendario(): BelongsTo
    {
        return $this->belongsTo(Calendario::class, 'calendario_id');
    }

    public function itemDespacho(): BelongsTo
    {
        return $this->belongsTo(ItemDespacho::class, 'item_despacho_id');
    }
}
