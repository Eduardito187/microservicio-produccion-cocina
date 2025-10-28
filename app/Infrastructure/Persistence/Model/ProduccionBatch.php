<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @package App\Infrastructure\Persistence\Model
 */
class ProduccionBatch extends BaseModel
{
    protected $table = 'produccion_batch';
    protected $guarded = [];
    protected $casts = [
        'ruta' => 'array',
        'rendimiento' => 'decimal:2',
    ];

    /**
     * @return BelongsTo
     */
    public function ordenProduccion(): BelongsTo
    {
        return $this->belongsTo(OrdenProduccion::class, 'op_id');
    }

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'p_id');
    }

    /**
     * @return BelongsTo
     */
    public function estacion(): BelongsTo
    {
        return $this->belongsTo(Estacion::class, 'estacion_id');
    }

    /**
     * @return BelongsTo
     */
    public function recetaVersion(): BelongsTo
    {
        return $this->belongsTo(RecetaVersion::class, 'receta_version_id');
    }

    /**
     * @return BelongsTo
     */
    public function porcion(): BelongsTo
    {
        return $this->belongsTo(Porcion::class, 'porcion_id');
    }

    /**
     * @return HasMany
     */
    public function etiquetas(): HasMany
    {
        return $this->hasMany(Etiqueta::class, 'lote_id');
    }
}