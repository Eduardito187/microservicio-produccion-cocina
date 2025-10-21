<?php

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Model;

class Outbox extends Model
{
  protected $table = 'outbox';
  protected $primaryKey = 'id';
  public $incrementing = true;
  protected $keyType = 'int';
  protected $fillable = ['event_name', 'aggregate_id', 'payload', 'occurred_on', 'published_at'];
  protected $casts = ['payload' => 'array', 'occurred_on'=>'datetime', 'published_at'=>'datetime'];
  public $timestamps = true;
}