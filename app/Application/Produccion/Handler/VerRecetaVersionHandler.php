<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\RecetaVersionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Command\VerRecetaVersion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Domain\Produccion\Entity\RecetaVersion;

/**
 * @class VerRecetaVersionHandler
 * @package App\Application\Produccion\Handler
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
