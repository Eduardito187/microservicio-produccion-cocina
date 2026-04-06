<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @class PayloadHelperTest
 */
class PayloadHelperTest extends TestCase
{
    // ─── getString ──────────────────────────────────────────────────────────

    public function test_get_string_retorna_primer_clave_encontrada(): void
    {
        $p = new Payload(['nombre' => 'Carlos', 'name' => 'Carlos EN']);
        $this->assertSame('Carlos', $p->getString(['nombre', 'name']));
    }

    public function test_get_string_usa_segunda_clave_si_primera_falta(): void
    {
        $p = new Payload(['name' => 'Pedro']);
        $this->assertSame('Pedro', $p->getString(['nombre', 'name']));
    }

    public function test_get_string_retorna_null_si_ninguna_clave_existe(): void
    {
        $p = new Payload([]);
        $this->assertNull($p->getString(['clave_inexistente']));
    }

    public function test_get_string_retorna_default_si_clave_falta(): void
    {
        $p = new Payload([]);
        $this->assertSame('default_val', $p->getString(['no_existe'], 'default_val'));
    }

    public function test_get_string_convierte_int_a_string(): void
    {
        $p = new Payload(['numero' => 42]);
        $this->assertSame('42', $p->getString(['numero']));
    }

    public function test_get_string_convierte_float_a_string(): void
    {
        $p = new Payload(['valor' => 3.14]);
        $this->assertSame('3.14', $p->getString(['valor']));
    }

    public function test_get_string_retorna_null_para_valor_vacio(): void
    {
        $p = new Payload(['campo' => '']);
        $this->assertNull($p->getString(['campo']));
    }

    public function test_get_string_retorna_null_para_valor_null(): void
    {
        $p = new Payload(['campo' => null]);
        $this->assertNull($p->getString(['campo']));
    }

    public function test_get_string_required_lanza_excepcion_si_falta(): void
    {
        $p = new Payload([]);
        $this->expectException(InvalidArgumentException::class);
        $p->getString(['campo_requerido'], null, true);
    }

    public function test_get_string_required_lanza_excepcion_para_valor_vacio(): void
    {
        $p = new Payload(['campo' => '']);
        $this->expectException(InvalidArgumentException::class);
        $p->getString(['campo'], null, true);
    }

    public function test_get_string_required_lanza_excepcion_para_tipo_no_string(): void
    {
        $p = new Payload(['campo' => ['array_value']]);
        $this->expectException(InvalidArgumentException::class);
        $p->getString(['campo'], null, true);
    }

    public function test_get_string_retorna_default_para_tipo_no_string_sin_required(): void
    {
        $p = new Payload(['campo' => ['array_value']]);
        $this->assertNull($p->getString(['campo']));
    }

    // ─── getArray ───────────────────────────────────────────────────────────

    public function test_get_array_retorna_array(): void
    {
        $p = new Payload(['items' => ['a', 'b', 'c']]);
        $this->assertSame(['a', 'b', 'c'], $p->getArray(['items']));
    }

    public function test_get_array_retorna_null_si_no_es_array(): void
    {
        $p = new Payload(['items' => 'not_an_array']);
        $this->assertNull($p->getArray(['items']));
    }

    public function test_get_array_retorna_default_si_clave_falta(): void
    {
        $p = new Payload([]);
        $this->assertSame(['default'], $p->getArray(['no_existe'], ['default']));
    }

    public function test_get_array_usa_segunda_clave(): void
    {
        $p = new Payload(['ingredientes' => ['sal', 'azucar']]);
        $this->assertSame(['sal', 'azucar'], $p->getArray(['ingredients', 'ingredientes']));
    }

    // ─── getInt ─────────────────────────────────────────────────────────────

    public function test_get_int_retorna_entero(): void
    {
        $p = new Payload(['cantidad' => 5]);
        $this->assertSame(5, $p->getInt(['cantidad']));
    }

    public function test_get_int_convierte_string_numerico(): void
    {
        $p = new Payload(['cantidad' => '10']);
        $this->assertSame(10, $p->getInt(['cantidad']));
    }

    public function test_get_int_retorna_null_para_string_no_numerico(): void
    {
        $p = new Payload(['cantidad' => 'no_numero']);
        $this->assertNull($p->getInt(['cantidad']));
    }

    public function test_get_int_retorna_null_si_clave_falta(): void
    {
        $p = new Payload([]);
        $this->assertNull($p->getInt(['no_existe']));
    }

    public function test_get_int_retorna_default_si_clave_falta(): void
    {
        $p = new Payload([]);
        $this->assertSame(99, $p->getInt(['no_existe'], 99));
    }

    public function test_get_int_usa_segunda_clave(): void
    {
        $p = new Payload(['totalCalories' => 350]);
        $this->assertSame(350, $p->getInt(['total_calories', 'totalCalories']));
    }
}
