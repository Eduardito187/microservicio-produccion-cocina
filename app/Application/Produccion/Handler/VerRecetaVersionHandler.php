<?php

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\VerRecetaVersion;
use App\Domain\Produccion\Repository\RecetaVersionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\RecetaVersion;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class VerRecetaVersionHandler
{
    /**
     * @var RecetaVersionRepositoryInterface
     */
    public readonly RecetaVersionRepositoryInterface $recetaVersionRepository;

    /**
     * @var TransactionAggregate
     */
    private readonly TransactionAggregate $transactionAggregate;

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
     * @param VerRecetaVersion $command
     * @return array
     */
    public function __invoke(VerRecetaVersion $command): array
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): array {
            $recetaVersion = $this->recetaVersionRepository->byId($command->id);
            return $this->mapReceta($recetaVersion);
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








