<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @class Etiqueta
 */
class Etiqueta extends BaseModel
{
    /**
     * @var mixed
     */
    protected $table = 'etiqueta';
    /**
     * @var mixed
     */
    protected $guarded = [];

    protected $casts = [
        'qr_payload' => 'array',
    ];

    public function suscripcion(): BelongsTo
    {
        return $this->belongsTo(Suscripcion::class, 'suscripcion_id');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function paquete(): HasOne
    {
        return $this->hasOne(Paquete::class, 'etiqueta_id');
    }
}
