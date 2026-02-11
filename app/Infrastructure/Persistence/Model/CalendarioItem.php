<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @class CalendarioItem
 * @package App\Infrastructure\Persistence\Model
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

    /**
     * @return BelongsTo
     */
    public function calendario(): BelongsTo
    {
        return $this->belongsTo(Calendario::class, 'calendario_id');
    }

    /**
     * @return BelongsTo
     */
    public function itemDespacho(): BelongsTo
    {
        return $this->belongsTo(ItemDespacho::class, 'item_despacho_id');
    }
}
