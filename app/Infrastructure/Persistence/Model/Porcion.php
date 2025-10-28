<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @package App\Infrastructure\Persistence\Model
 */
class Porcion extends BaseModel
{
    protected $table = 'porcion';
    protected $guarded = [];

    /**
     * @return HasMany
     */
    public function batches(): HasMany
    {
        return $this->hasMany(ProduccionBatch::class, 'porcion_id');
    }
}