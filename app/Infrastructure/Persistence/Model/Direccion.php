<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @class Direccion
 */
class Direccion extends BaseModel
{
    /**
     * @var mixed
     */
    protected $table = 'direccion';
    /**
     * @var mixed
     */
    protected $guarded = [];

    protected $casts = [
        'geo' => 'array',
    ];

    public function paquetes(): HasMany
    {
        return $this->hasMany(Paquete::class, 'direccion_id');
    }
}
