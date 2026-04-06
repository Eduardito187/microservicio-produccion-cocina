<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Produccion;

use App\Application\Produccion\Command\ActualizarRecetaVersion;
use App\Application\Produccion\Command\CrearRecetaVersion;
use App\Application\Produccion\Command\EliminarRecetaVersion;
use App\Application\Produccion\Command\ListarRecetasVersion;
use App\Application\Produccion\Command\VerRecetaVersion;
use App\Application\Produccion\Handler\ActualizarRecetaVersionHandler;
use App\Application\Produccion\Handler\CrearRecetaVersionHandler;
use App\Application\Produccion\Handler\EliminarRecetaVersionHandler;
use App\Application\Produccion\Handler\ListarRecetasVersionHandler;
use App\Application\Produccion\Handler\VerRecetaVersionHandler;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Application\Support\Transaction\Interface\TransactionManagerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\RecetaVersion;
use App\Domain\Produccion\Repository\RecetaVersionRepositoryInterface;
use PHPUnit\Framework\TestCase;

/**
 * @class RecetaVersionHandlersTest
 */
class RecetaVersionHandlersTest extends TestCase
{
    private function tx(): TransactionAggregate
    {
        $manager = new class implements TransactionManagerInterface
        {
            public function run(callable $callback): mixed
            {
                return $callback();
            }

            public function afterCommit(callable $callback): void {}
        };

        return new TransactionAggregate($manager);
    }

    // ─── CrearRecetaVersionHandler ─────────────────────────────────────────

    public function test_crear_receta_version_handler_guarda_y_publica_evento(): void
    {
        $repo = $this->createMock(RecetaVersionRepositoryInterface::class);
        $publisher = $this->createMock(DomainEventPublisherInterface::class);

        $repo->expects($this->once())->method('save')
            ->with($this->callback(fn (RecetaVersion $r) => $r->nombre === 'Dieta Proteica' && $r->id === null))
            ->willReturn('new-uuid-001');

        $publisher->expects($this->once())->method('publish')
            ->with($this->isType('array'), 'new-uuid-001');

        $handler = new CrearRecetaVersionHandler($repo, $this->tx(), $publisher);
        $id = $handler(new CrearRecetaVersion('Dieta Proteica', ['kcal' => 400], [['sal']], 'desc', 'instr', 400));

        $this->assertSame('new-uuid-001', $id);
    }

    public function test_crear_receta_version_handler_retorna_id_generado_por_repo(): void
    {
        $repo = $this->createMock(RecetaVersionRepositoryInterface::class);
        $publisher = $this->createMock(DomainEventPublisherInterface::class);

        $repo->method('save')->willReturn('generated-id-999');

        $handler = new CrearRecetaVersionHandler($repo, $this->tx(), $publisher);
        $id = $handler(new CrearRecetaVersion('Receta Simple'));

        $this->assertSame('generated-id-999', $id);
    }

    public function test_crear_receta_version_handler_acepta_campos_opcionales_nulos(): void
    {
        $repo = $this->createMock(RecetaVersionRepositoryInterface::class);
        $publisher = $this->createMock(DomainEventPublisherInterface::class);

        $repo->expects($this->once())->method('save')
            ->with($this->callback(fn (RecetaVersion $r) => $r->nutrientes === null
                && $r->ingredientes === null
                && $r->description === null
                && $r->totalCalories === null
            ))
            ->willReturn('id-001');

        $handler = new CrearRecetaVersionHandler($repo, $this->tx(), $publisher);
        $handler(new CrearRecetaVersion('Minima'));
    }

    // ─── ActualizarRecetaVersionHandler ───────────────────────────────────

    public function test_actualizar_receta_version_handler_actualiza_y_publica_evento(): void
    {
        $existing = new RecetaVersion('r-01', 'Nombre Viejo', null, null, null, null, null);
        $repo = $this->createMock(RecetaVersionRepositoryInterface::class);
        $publisher = $this->createMock(DomainEventPublisherInterface::class);

        $repo->method('byId')->with('r-01')->willReturn($existing);
        $repo->expects($this->once())->method('save')
            ->with($this->callback(fn (RecetaVersion $r) => $r->nombre === 'Nombre Nuevo' && $r->totalCalories === 350
            ))
            ->willReturn('r-01');

        $publisher->expects($this->once())->method('publish')
            ->with($this->isType('array'), 'r-01');

        $handler = new ActualizarRecetaVersionHandler($repo, $this->tx(), $publisher);
        $id = $handler(new ActualizarRecetaVersion('r-01', 'Nombre Nuevo', null, null, null, null, 350));

        $this->assertSame('r-01', $id);
    }

    public function test_actualizar_receta_version_handler_sobrescribe_todos_campos(): void
    {
        $existing = new RecetaVersion('r-02', 'Original', ['kcal' => 100], null, null, null, 100);
        $repo = $this->createMock(RecetaVersionRepositoryInterface::class);
        $publisher = $this->createMock(DomainEventPublisherInterface::class);

        $repo->method('byId')->willReturn($existing);
        $repo->method('save')->willReturn('r-02');

        $handler = new ActualizarRecetaVersionHandler($repo, $this->tx(), $publisher);
        $handler(new ActualizarRecetaVersion(
            'r-02',
            'Actualizada',
            ['kcal' => 500],
            [['veggie']],
            'nueva desc',
            'nuevas instr',
            500
        ));

        $this->assertSame('Actualizada', $existing->nombre);
        $this->assertSame(['kcal' => 500], $existing->nutrientes);
        $this->assertSame(500, $existing->totalCalories);
    }

    // ─── EliminarRecetaVersionHandler ─────────────────────────────────────

    public function test_eliminar_receta_version_handler_llama_byid_y_delete(): void
    {
        $repo = $this->createMock(RecetaVersionRepositoryInterface::class);

        $repo->expects($this->once())->method('byId')->with('r-del-01');
        $repo->expects($this->once())->method('delete')->with('r-del-01');

        $handler = new EliminarRecetaVersionHandler($repo, $this->tx());
        $handler(new EliminarRecetaVersion('r-del-01'));
    }

    // ─── VerRecetaVersionHandler ───────────────────────────────────────────

    public function test_ver_receta_version_handler_retorna_array_con_todos_campos(): void
    {
        $entity = new RecetaVersion(
            'r-ver-01',
            'Mi Receta',
            ['kcal' => 300],
            [['arroz']],
            'Descripcion',
            'Instrucciones',
            300
        );
        $repo = $this->createMock(RecetaVersionRepositoryInterface::class);
        $repo->method('byId')->with('r-ver-01')->willReturn($entity);

        $handler = new VerRecetaVersionHandler($repo, $this->tx());
        $result = $handler(new VerRecetaVersion('r-ver-01'));

        $this->assertSame('r-ver-01', $result['id']);
        $this->assertSame('Mi Receta', $result['nombre']);
        $this->assertSame('Mi Receta', $result['name']);
        $this->assertSame(['kcal' => 300], $result['nutrientes']);
        $this->assertSame([['arroz']], $result['ingredientes']);
        $this->assertSame([['arroz']], $result['ingredients']);
        $this->assertSame('Descripcion', $result['description']);
        $this->assertSame('Instrucciones', $result['instructions']);
        $this->assertSame(300, $result['totalCalories']);
    }

    // ─── ListarRecetasVersionHandler ──────────────────────────────────────

    public function test_listar_recetas_version_handler_retorna_array_de_recetas(): void
    {
        $entities = [
            new RecetaVersion('r-l-01', 'Receta A', null, null, 'descA', null, null),
            new RecetaVersion('r-l-02', 'Receta B', ['kcal' => 200], null, 'descB', 'instrB', 200),
        ];
        $repo = $this->createMock(RecetaVersionRepositoryInterface::class);
        $repo->method('list')->willReturn($entities);

        $handler = new ListarRecetasVersionHandler($repo, $this->tx());
        $result = $handler(new ListarRecetasVersion);

        $this->assertCount(2, $result);
        $this->assertSame('r-l-01', $result[0]['id']);
        $this->assertSame('Receta A', $result[0]['nombre']);
        $this->assertSame('r-l-02', $result[1]['id']);
        $this->assertSame('Receta B', $result[1]['nombre']);
        $this->assertSame(200, $result[1]['totalCalories']);
    }

    public function test_listar_recetas_version_handler_retorna_array_vacio_si_no_hay_recetas(): void
    {
        $repo = $this->createMock(RecetaVersionRepositoryInterface::class);
        $repo->method('list')->willReturn([]);

        $handler = new ListarRecetasVersionHandler($repo, $this->tx());
        $result = $handler(new ListarRecetasVersion);

        $this->assertSame([], $result);
    }
}
