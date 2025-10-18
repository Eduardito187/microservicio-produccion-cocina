<?php

namespace App\Infrastructure\Persistence\Eloquent\Model;

use App\Infrastructure\Persistence\Eloquent\Model\ItemDespacho;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class ListaDespacho extends Model
{
    protected $table = 'lista_despacho';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = ['op_id', 'fecha_entrega', 'sucursal_id'];
    public $timestamps = true;

    /**
     * @return HasMany
     */
    public function items() : HasMany
    {
        return $this->hasMany(ItemDespacho::class, 'lista_id', 'id');
    }
}