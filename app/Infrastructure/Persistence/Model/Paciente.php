<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @class Paciente
 */
class Paciente extends BaseModel
{
    /**
     * @var mixed
     */
    protected $table = 'paciente';
    /**
     * @var mixed
     */
    protected $guarded = [];

    public function suscripcion(): BelongsTo
    {
        return $this->belongsTo(Suscripcion::class, 'suscripcion_id');
    }

    public function etiquetas(): HasMany
    {
        return $this->hasMany(Etiqueta::class, 'paciente_id');
    }
}
