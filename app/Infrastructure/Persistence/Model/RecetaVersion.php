<?php

namespace App\Infrastructure\Persistence\Model;

class RecetaVersion extends BaseModel
{
    protected $table = 'receta_version';
    protected $guarded = [];

    protected $casts = [
        'nutrientes' => 'array',
        'ingredientes' => 'array',
    ];

    public function batches()
    {
        return $this->hasMany(ProduccionBatch::class, 'receta_version_id');
    }

    public function etiquetas()
    {
        return $this->hasMany(Etiqueta::class, 'receta_version_id');
    }
}