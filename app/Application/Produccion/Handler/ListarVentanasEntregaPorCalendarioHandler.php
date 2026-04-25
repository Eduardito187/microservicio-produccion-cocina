<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\ListarVentanasEntregaPorCalendario;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\VentanaEntrega;
use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;
use App\Domain\Produccion\Repository\VentanaEntregaRepositoryInterface;

/**
 * @class ListarVentanasEntregaPorCalendarioHandler
 */
class ListarVentanasEntregaPorCalendarioHandler
{
    /**
     * @var VentanaEntregaRepositoryInterface
     */
    private $ventanaEntregaRepository;

    /**
     * @var CalendarioRepositoryInterface
     */
    private $calendarioRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    public function __construct(
        VentanaEntregaRepositoryInterface $ventanaEntregaRepository,
        CalendarioRepositoryInterface $calendarioRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->ventanaEntregaRepository = $ventanaEntregaRepository;
        $this->calendarioRepository = $calendarioRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    public function __invoke(ListarVentanasEntregaPorCalendario $command): array
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): array {
            $this->calendarioRepository->byId($command->calendarioId);

            return array_map(
                [$this, 'mapVentana'],
                $this->ventanaEntregaRepository->byCalendarioId($command->calendarioId)
            );
        });
    }

    private function mapVentana(VentanaEntrega $ventana): array
    {
        return [
            'id' => $ventana->id,
            'desde' => $ventana->desde->format('Y-m-d H:i:s'),
            'hasta' => $ventana->hasta->format('Y-m-d H:i:s'),
            'entrega_id' => $ventana->entregaId,
            'contrato_id' => $ventana->contratoId,
            'estado' => $ventana->estado,
        ];
    }
}
