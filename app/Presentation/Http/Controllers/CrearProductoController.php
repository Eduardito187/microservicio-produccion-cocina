<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Command\CrearProducto;
use App\Application\Produccion\Handler\CrearProductoHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class CrearProductoController
 */
class CrearProductoController
{
    /**
     * @var CrearProductoHandler
     */
    private $handler;

    /**
     * Constructor
     */
    public function __construct(CrearProductoHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:150'],
            'nombre' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'specialPrice' => ['nullable', 'numeric', 'min:0'],
        ]);

        $productId = $this->handler->__invoke(new CrearProducto(
            $data['sku'],
            (float) $data['price'],
            isset($data['specialPrice']) ? (float) $data['specialPrice'] : 0.0,
            $data['nombre'] ?? null
        ));

        return response()->json(['productId' => $productId], 201);
    }
}
