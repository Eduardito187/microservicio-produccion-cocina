<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @class Paquete
 * @package App\Infrastructure\Persistence\Model
 */
class Paquete extends BaseModel
{
    /**
     * @var mixed
     */
    protected $table = 'paquete';
    /**
     * @var mixed
     */
    protected $guarded = [];

    /**
     * @return BelongsTo
     */
    public function etiqueta(): BelongsTo
    {
        return $this->belongsTo(Etiqueta::class, 'etiqueta_id');
    }

    /**
     * @return BelongsTo
     */
    public function ventana(): BelongsTo
    {
        return $this->belongsTo(VentanaEntrega::class, 'ventana_id');
    }

    /**
     * @return BelongsTo
     */
    public function direccion(): BelongsTo
    {
        return $this->belongsTo(Direccion::class, 'direccion_id');
    }

    /**
     * @return HasMany
     */
    public function itemsDespacho(): HasMany
    {
        return $this->hasMany(ItemDespacho::class, 'paquete_id');
    }
}
