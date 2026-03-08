<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\ListarRecetasVersion;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\RecetaVersion;
use App\Domain\Produccion\Repository\RecetaVersionRepositoryInterface;

/**
 * @class ListarRecetasVersionHandler
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
     */
    public function __construct(
        RecetaVersionRepositoryInterface $recetaVersionRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->recetaVersionRepository = $recetaVersionRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    public function __invoke(ListarRecetasVersion $command): array
    {
        return $this->transactionAggregate->runTransaction(function (): array {
            return array_map([$this, 'mapReceta'], $this->recetaVersionRepository->list());
        });
    }

    private function mapReceta(RecetaVersion $recetaVersion): array
    {
        return [
            'id' => $recetaVersion->id,
            'nombre' => $recetaVersion->nombre,
            'name' => $recetaVersion->nombre,
            'nutrientes' => $recetaVersion->nutrientes,
            'ingredientes' => $recetaVersion->ingredientes,
            'ingredients' => $recetaVersion->ingredientes,
            'description' => $recetaVersion->description,
            'instructions' => $recetaVersion->instructions,
            'totalCalories' => $recetaVersion->totalCalories,
        ];
    }
}
