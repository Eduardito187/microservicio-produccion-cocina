<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @package App\Infrastructure\Persistence\Model
 */
class VentanaEntrega extends BaseModel
{
    protected $table = 'ventana_entrega';
    protected $guarded = [];
    protected $casts = [
        'desde' => 'datetime',
        'hasta' => 'datetime',
    ];

    /**
     * @return HasMany
     */
    public function paquetes(): HasMany
    {
        return $this->hasMany(Paquete::class, 'ventana_id');
    }
}