<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\CrearProductoHandler;
use App\Application\Produccion\Command\CrearProducto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class CrearProductoController
 * @package App\Presentation\Http\Controllers
 */
class CrearProductoController
{
    /**
     * @var CrearProductoHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param CrearProductoHandler $handler
     */
    public function __construct(CrearProductoHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:150'],
            'price' => ['required', 'numeric', 'min:0'],
            'specialPrice' => ['nullable', 'numeric', 'min:0'],
        ]);

        $productId = $this->handler->__invoke(new CrearProducto(
            $data['sku'],
            (float) $data['price'],
            isset($data['specialPrice']) ? (float) $data['specialPrice'] : 0.0
        ));

        return response()->json(['productId' => $productId], 201);
    }
}
