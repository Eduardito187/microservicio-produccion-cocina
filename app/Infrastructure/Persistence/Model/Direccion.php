<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @package App\Infrastructure\Persistence\Model
 */
class Direccion extends BaseModel
{
    protected $table = 'direccion';
    protected $guarded = [];

    protected $casts = [
        'geo' => 'array',
    ];

    /**
     * @return HasMany
     */
    public function paquetes(): HasMany
    {
        return $this->hasMany(Paquete::class, 'direccion_id');
    }
}