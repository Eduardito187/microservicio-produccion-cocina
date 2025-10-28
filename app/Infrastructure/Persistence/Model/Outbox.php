<?php

namespace App\Infrastructure\Persistence\Model;

class Outbox extends BaseModel
{
    protected $table = 'outbox';
    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'occurred_on' => 'datetime',
        'published_at' => 'datetime',
    ];
}