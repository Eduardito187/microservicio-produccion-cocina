<?php

namespace App\Infrastructure\Persistence\Model;

class Etiqueta extends BaseModel
{
    protected $table = 'etiqueta';
    protected $guarded = [];

    protected $casts = [
        'qr_payload' => 'array',
    ];

    public function ordenProduccion()
    {
        return $this->belongsTo(OrdenProduccion::class, 'orden_produccion_id');
    }

    // In schema, lote_id references produccion_batch
    public function lote()
    {
        return $this->belongsTo(ProduccionBatch::class, 'lote_id');
    }

    public function recetaVersion()
    {
        return $this->belongsTo(RecetaVersion::class, 'receta_version_id');
    }

    public function suscripcion()
    {
        return $this->belongsTo(Suscripcion::class, 'suscripcion_id');
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function paquete()
    {
        return $this->hasOne(Paquete::class, 'etiqueta_id');
    }
}