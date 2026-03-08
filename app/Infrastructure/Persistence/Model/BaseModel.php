<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

use App\Infrastructure\Persistence\Model\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;

/**
 * @class BaseModel
 */
class BaseModel extends Model
{
    use HasUuid;

    /**
     * @var mixed
     */
    protected $primaryKey = 'id';
    /**
     * @var mixed
     */
    public $incrementing = false;
    /**
     * @var mixed
     */
    protected $keyType = 'string';
    /**
     * @var mixed
     */
    protected $fillable = [];
    /**
     * @var mixed
     */
    public $timestamps = true;
}
