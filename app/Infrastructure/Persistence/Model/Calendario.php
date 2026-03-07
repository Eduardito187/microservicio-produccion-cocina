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
        'estado' => 'integer',
    ];

    public function setFechaAttribute(mixed $value): void
    {
        if ($value === null) {
            $this->attributes['fecha'] = null;
            return;
        }
        $this->attributes['fecha'] = \Carbon\Carbon::parse($value)->format('Y-m-d');
    }

    /**
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(CalendarioItem::class, 'calendario_id');
    }
}
