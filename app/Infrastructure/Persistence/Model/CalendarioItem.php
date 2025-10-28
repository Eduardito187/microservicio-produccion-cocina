<?php

namespace App\Infrastructure\Persistence\Model;

class CalendarioItem extends BaseModel
{
    protected $table = 'calendario_item';
    protected $guarded = [];

    public function calendario()
    {
        return $this->belongsTo(Calendario::class, 'calendario_id');
    }

    public function itemDespacho()
    {
        return $this->belongsTo(ItemDespacho::class, 'item_despacho_id');
    }
}