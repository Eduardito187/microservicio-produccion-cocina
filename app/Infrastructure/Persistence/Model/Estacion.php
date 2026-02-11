<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @class Estacion
 * @package App\Infrastructure\Persistence\Model
 */
class Estacion extends BaseModel
{
    /**
     * @var mixed
     */
    protected $table = 'estacion';
    /**
     * @var mixed
     */
    protected $guarded = [];

    /**
     * @return HasMany
     */
    public function batches(): HasMany
    {
        return $this->hasMany(ProduccionBatch::class, 'estacion_id');
    }
}
