<?php

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RecetaVersion extends Model
{
    use HasFactory;
    protected $table = 'receta_version';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;
    protected $fillable = [
        'nombre',
        'nutrientes',
        'ingredientes',
        'version',
    ];
    protected $casts = [
        'nutrientes' => 'array',
        'ingredientes' => 'array',
        'version' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}