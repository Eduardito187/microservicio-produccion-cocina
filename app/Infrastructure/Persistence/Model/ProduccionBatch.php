<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @class ProduccionBatch
 * @package App\Infrastructure\Persistence\Model
 */
class ProduccionBatch extends BaseModel
{
    /**
     * @var mixed
     */
    protected $table = 'produccion_batch';
    /**
     * @var mixed
     */
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
    public function porcion(): BelongsTo
    {
        return $this->belongsTo(Porcion::class, 'porcion_id');
    }

    /**
     * @return BelongsTo
     */
    public function receta(): BelongsTo
    {
        return $this->belongsTo(Receta::class, 'receta_id');
    }
}
