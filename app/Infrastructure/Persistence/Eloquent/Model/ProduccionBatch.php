<?php

namespace App\Infrastructure\Persistence\Eloquent\Model;

use App\Infrastructure\Persistence\Eloquent\Model\OrdenProduccion;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class ProduccionBatch extends Model
{
  protected $table = 'produccion_batch';
  protected $primaryKey = 'id';
  public $incrementing = true;
  protected $keyType = 'int';
  protected $fillable = ['op_id', 'estacion_id', 'receta_version_id', 'porcion_id', 'cant_planificada', 'cant_producida', 'merma_gr', 'estado', 'sku', 'qty', 'posicion', 'ruta'];
  protected $casts = ['qty' => 'int', 'posicion'=> 'int', 'ruta'=> 'array'];
  public $timestamps = true;

  /**
   * @return BelongsTo
   */
  public function ordenProduccion() : BelongsTo
  {
    return $this->belongsTo(OrdenProduccion::class, 'op_id');
  }
}