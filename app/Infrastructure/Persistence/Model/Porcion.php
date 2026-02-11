<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @class Porcion
 * @package App\Infrastructure\Persistence\Model
 */
class Porcion extends BaseModel
{
    /**
     * @var mixed
     */
    protected $table = 'porcion';
    /**
     * @var mixed
     */
    protected $guarded = [];

    /**
     * @return HasMany
     */
    public function batches(): HasMany
    {
        return $this->hasMany(ProduccionBatch::class, 'porcion_id');
    }
}
