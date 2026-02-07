<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Model;
use App\Infrastructure\Persistence\Model\Concerns\HasUuid;

/**
 * @package App\Infrastructure\Persistence\Model
 */
class BaseModel extends Model
{
    use HasUuid;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [];
    public $timestamps = true;
}
