<?php

namespace App\Domain\Produccion\Enum;

enum EstadoPlanificado: string
{
    case PROGRAMADO = 'PROGRAMADO';
    case PROCESANDO = 'PROCESANDO';
    case DESPACHADO = 'DESPACHADO';
}