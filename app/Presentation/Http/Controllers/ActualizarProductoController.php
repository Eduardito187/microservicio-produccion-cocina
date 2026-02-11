<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use App\Application\Produccion\Handler\ActualizarProductoHandler;
use App\Application\Produccion\Command\ActualizarProducto;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @class ActualizarProductoController
 * @package App\Presentation\Http\Controllers
 */
class ActualizarProductoController
{
    /**
     * @var ActualizarProductoHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param ActualizarProductoHandler $handler
     */
    public function __construct(ActualizarProductoHandler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function __invoke(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:150'],
            'price' => ['required', 'numeric', 'min:0'],
            'specialPrice' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $productId = $this->handler->__invoke(new ActualizarProducto(
                $id,
                $data['sku'],
                (float) $data['price'],
                isset($data['specialPrice']) ? (float) $data['specialPrice'] : 0.0
            ));

            return response()->json(['productId' => $productId], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
