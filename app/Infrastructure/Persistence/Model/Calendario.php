<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @class Calendario
 * @package App\Infrastructure\Persistence\Model
 */
class Calendario extends BaseModel
{
    /**
     * @var mixed
     */
    protected $table = 'calendario';
    /**
     * @var mixed
     */
    protected $guarded = [];

    protected $casts = [
        'fecha' => 'date',
    ];

    /**
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(CalendarioItem::class, 'calendario_id');
    }
}
