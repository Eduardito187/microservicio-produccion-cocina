<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Produccion\Repository\RecetaRepositoryInterface;

class RecetaRepository extends RecetaVersionRepository implements RecetaRepositoryInterface
{
}
