<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\VerRecetaVersion;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\RecetaVersion;
use App\Domain\Produccion\Repository\RecetaVersionRepositoryInterface;

/**
 * @class VerRecetaVersionHandler
 */
class VerRecetaVersionHandler
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

    public function __invoke(VerRecetaVersion $command): array
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): array {
            $recetaVersion = $this->recetaVersionRepository->byId($command->id);

            return $this->mapReceta($recetaVersion);
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
