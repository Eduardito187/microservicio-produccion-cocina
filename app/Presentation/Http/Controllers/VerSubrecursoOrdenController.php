<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Infrastructure\Persistence\Query\OrdenProduccionQueryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Devuelve un sub-recurso de una orden consolidada (items, batches, despacho, progreso-entrega).
 *
 * @class VerSubrecursoOrdenController
 */
class VerSubrecursoOrdenController
{
    private const SUBRECURSOS = ['items', 'batches', 'despacho', 'progreso-entrega'];

    /**
     * @var OrdenProduccionQueryRepository
     */
    private $query;

    /**
     * Constructor
     */
    public function __construct(OrdenProduccionQueryRepository $query)
    {
        $this->query = $query;
    }

    public function __invoke(Request $request, string $id, string $sub): JsonResponse
    {
        $key = str_replace('-', '_', $sub);

        if (! in_array($sub, self::SUBRECURSOS, true)) {
            return response()->json(['message' => "Sub-recurso '{$sub}' no reconocido."], 404);
        }

        try {
            $orden = $this->query->porId($id);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

        if ($orden === null) {
            return response()->json(['message' => "La orden de produccion id: {$id} no existe."], 404);
        }

        return response()->json($orden[$key] ?? null);
    }
}
