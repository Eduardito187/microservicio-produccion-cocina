<?php

namespace App\Infrastructure\Persistence\Model;

class ProduccionBatch extends BaseModel
{
    protected $table = 'produccion_batch';
    protected $guarded = [];

    protected $casts = [
        'ruta' => 'array',
        'rendimiento' => 'decimal:2',
    ];

    public function ordenProduccion()
    {
        return $this->belongsTo(OrdenProduccion::class, 'op_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'p_id');
    }

    public function estacion()
    {
        return $this->belongsTo(Estacion::class, 'estacion_id');
    }

    public function recetaVersion()
    {
        return $this->belongsTo(RecetaVersion::class, 'receta_version_id');
    }

    public function porcion()
    {
        return $this->belongsTo(Porcion::class, 'porcion_id');
    }

    // Nota: en tu esquema, etiqueta.lote_id referencia produccion_batch.id
    public function etiquetas()
    {
        return $this->hasMany(Etiqueta::class, 'lote_id');
    }
}