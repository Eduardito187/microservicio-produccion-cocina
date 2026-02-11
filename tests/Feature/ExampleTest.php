<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature;

use Tests\TestCase;

/**
 * @class ExampleTest
 * @package Tests\Feature
 */
class ExampleTest extends TestCase
{
    /**
     * @return void
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }
}
