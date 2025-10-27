<?php

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Estacion extends Model
{
    use HasFactory;

    protected $table = 'estacion';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;
    protected $fillable = [
        'nombre',
        'capacidad',
    ];
    protected $casts = [
        'capacidad' => 'integer',
        'created_at'=> 'datetime',
        'updated_at'=> 'datetime',
    ];
}