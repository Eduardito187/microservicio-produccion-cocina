<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

/**
 * Identifica el contexto de entrega asociado a un paquete (op, entrega, contrato).
 *
 * @class DeliveryContextIds
 */
class DeliveryContextIds
{
    public function __construct(
        public readonly ?string $opId,
        public readonly ?string $entregaId,
        public readonly ?string $contratoId,
    ) {}
}
