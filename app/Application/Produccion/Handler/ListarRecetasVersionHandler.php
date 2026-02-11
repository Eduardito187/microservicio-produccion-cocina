<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\RecetaVersionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\ListarRecetasVersion;
use App\Domain\Produccion\Entity\RecetaVersion;

/**
 * @class ListarRecetasVersionHandler
 * @package App\Application\Produccion\Handler
 */
class ListarRecetasVersionHandler
{
    /**
     * @var RecetaVersionRepositoryInterface
     */
    private $recetaVersionRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    /**
     * Constructor
     *
     * @param RecetaVersionRepositoryInterface $recetaVersionRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        RecetaVersionRepositoryInterface $recetaVersionRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->recetaVersionRepository = $recetaVersionRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param ListarRecetasVersion $command
     * @return array
     */
    public function __invoke(ListarRecetasVersion $command): array
    {
        return $this->transactionAggregate->runTransaction(function (): array {
            return array_map([$this, 'mapReceta'], $this->recetaVersionRepository->list());
        });
    }

    /**
     * @param RecetaVersion $recetaVersion
     * @return array
     */
    private function mapReceta(RecetaVersion $recetaVersion): array
    {
        return [
            'id' => $recetaVersion->id,
            'nombre' => $recetaVersion->nombre,
            'nutrientes' => $recetaVersion->nutrientes,
            'ingredientes' => $recetaVersion->ingredientes,
            'version' => $recetaVersion->version,
        ];
    }
}
