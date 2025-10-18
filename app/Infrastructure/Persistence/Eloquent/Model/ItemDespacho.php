<?php

namespace App\Infrastructure\Persistence\Eloquent\Model;

use App\Infrastructure\Persistence\Eloquent\Model\ListaDespacho;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class ItemDespacho extends Model
{
  protected $table = 'item_despacho';
  protected $primaryKey = 'id';
  public $incrementing = true;
  protected $keyType = 'int';
  protected $fillable = ['lista_id', 'sku', 'etiqueta_id', 'paciente_id', 'direccion_snapshot', 'ventana_entrega'];
    protected $casts = ['direccion_snapshot'=> 'array', 'ventana_entrega'=> 'array'];
  public $timestamps = true;

  /**
   * @return BelongsTo
   */
  public function ordenProduccion() : BelongsTo
  {
    return $this->belongsTo(ListaDespacho::class, 'lista_id');
  }
}