<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\ObtenerAgenda;
use App\Infrastructure\Persistence\Query\AgendaQueryRepository;

/**
 * @class ObtenerAgendaHandler
 */
class ObtenerAgendaHandler
{
    /**
     * @var AgendaQueryRepository
     */
    private $query;

    /**
     * Constructor
     */
    public function __construct(AgendaQueryRepository $query)
    {
        $this->query = $query;
    }

    public function __invoke(ObtenerAgenda $command): array
    {
        return $this->query->consolidada($command->fechaInicio, $command->fechaFin);
    }
}
