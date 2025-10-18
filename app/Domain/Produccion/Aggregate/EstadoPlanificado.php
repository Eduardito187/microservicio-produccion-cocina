<?php

namespace App\Domain\Produccion\Aggregate;

enum EstadoPlanificado: string
{
    case PROGRAMADO = 'PROGRAMADO';
    case PROCESANDO = 'PROCESANDO';
    case DESPACHADO = 'DESPACHADO';
}