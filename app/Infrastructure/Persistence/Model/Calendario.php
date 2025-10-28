<?php

namespace App\Infrastructure\Persistence\Model;

class Calendario extends BaseModel
{
    protected $table = 'calendario';
    protected $guarded = [];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(CalendarioItem::class, 'calendario_id');
    }
}