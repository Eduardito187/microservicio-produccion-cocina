<?php

namespace App\Domain\Produccion\Aggregate;

enum EstadoOP: string
{
    case CREADA = 'CREADA';
    case EN_PROCESO = 'EN_PROCESO';
    case CERRADA = 'CERRADA';
}