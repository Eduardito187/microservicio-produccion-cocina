<?php

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Direccion extends Model
{
    use HasFactory;

    protected $table = 'direccion';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;
    protected $fillable = [
        'nombre',
        'linea1',
        'linea2',
        'ciudad',
        'provincia',
        'pais',
        'latitud',
        'longitud',
    ];
    protected $casts = [
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
        'created_at'=> 'datetime',
        'updated_at'=> 'datetime',
    ];
}