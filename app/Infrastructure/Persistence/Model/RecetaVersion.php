<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @class RecetaVersion
 */
class RecetaVersion extends BaseModel
{
    /**
     * @var mixed
     */
    protected $table = 'receta';
    /**
     * @var mixed
     */
    protected $guarded = [];
    protected $casts = [
        'nutrientes' => 'array',
        'ingredientes' => 'array',
        'total_calories' => 'integer',
    ];

    public function batches(): HasMany
    {
        return $this->hasMany(ProduccionBatch::class, 'receta_id');
    }

    public function etiquetas(): HasMany
    {
        return $this->hasMany(Etiqueta::class, 'receta_id');
    }
}
