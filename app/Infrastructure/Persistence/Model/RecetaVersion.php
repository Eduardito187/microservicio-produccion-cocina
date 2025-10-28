<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @package App\Infrastructure\Persistence\Model
 */
class RecetaVersion extends BaseModel
{
    protected $table = 'receta_version';
    protected $guarded = [];
    protected $casts = [
        'nutrientes' => 'array',
        'ingredientes' => 'array',
    ];

    /**
     * @return HasMany
     */
    public function batches(): HasMany
    {
        return $this->hasMany(ProduccionBatch::class, 'receta_version_id');
    }

    /**
     * @return HasMany
     */
    public function etiquetas(): HasMany
    {
        return $this->hasMany(Etiqueta::class, 'receta_version_id');
    }
}