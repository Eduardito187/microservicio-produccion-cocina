<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @class Suscripcion
 * @package App\Infrastructure\Persistence\Model
 */
class Suscripcion extends BaseModel
{
    /**
     * @var mixed
     */
    protected $table = 'suscripcion';
    /**
     * @var mixed
     */
    protected $guarded = [];

    /**
     * @return HasMany
     */
    public function pacientes(): HasMany
    {
        return $this->hasMany(Paciente::class, 'suscripcion_id');
    }

    /**
     * @return HasMany
     */
    public function etiquetas(): HasMany
    {
        return $this->hasMany(Etiqueta::class, 'suscripcion_id');
    }
}
