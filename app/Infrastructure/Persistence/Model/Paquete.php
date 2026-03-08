<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @class Paquete
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

    public function etiqueta(): BelongsTo
    {
        return $this->belongsTo(Etiqueta::class, 'etiqueta_id');
    }

    public function ventana(): BelongsTo
    {
        return $this->belongsTo(VentanaEntrega::class, 'ventana_id');
    }

    public function direccion(): BelongsTo
    {
        return $this->belongsTo(Direccion::class, 'direccion_id');
    }

    public function itemsDespacho(): HasMany
    {
        return $this->hasMany(ItemDespacho::class, 'paquete_id');
    }
}
