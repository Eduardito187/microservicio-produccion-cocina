<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\ActualizarProducto;
use App\Application\Produccion\Handler\ActualizarProductoHandler;
use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class ActualizarProductoController
 */
class ActualizarProductoController
{
    /**
     * @var ActualizarProductoHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(ActualizarProductoHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:150'],
            'nombre' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'specialPrice' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $productId = $this->handler->__invoke(new ActualizarProducto(
                $id,
                $data['sku'],
                (float) $data['price'],
                isset($data['specialPrice']) ? (float) $data['specialPrice'] : 0.0,
                $data['nombre'] ?? null
            ));

            return response()->json(['productId' => $productId], 200);
        } catch (EntityNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
