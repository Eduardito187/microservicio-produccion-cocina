<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Console\Commands;

use App\Infrastructure\ServiceDiscovery\ConsulClient;
use App\Infrastructure\ServiceDiscovery\ServiceDefinitionFactory;
use Illuminate\Console\Command;

class ConsulRegisterCommand extends Command
{
    protected $signature = 'consul:register {--dry-run : Print the definition without calling Consul}';

    protected $description = 'Register this service with Consul service discovery';

    public function handle(): int
    {
        $config = (array) config('service-discovery');
        $driver = (string) ($config['driver'] ?? 'consul');
        if ($driver !== 'consul') {
            $this->warn('Service discovery driver is not "consul" (got "' . $driver . '"). Skipping.');

            return self::SUCCESS;
        }

        $consulConfig = (array) ($config['consul'] ?? []);
        $host = (string) ($consulConfig['host'] ?? '');
        if ($host === '') {
            $this->warn('CONSUL_HOST is empty; skipping registration.');

            return self::SUCCESS;
        }

        $definition = ServiceDefinitionFactory::fromConfig($config);

        if ($this->option('dry-run')) {
            $this->line(json_encode($definition, JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        try {
            $client = new ConsulClient($consulConfig);
            $client->register($definition);
            $this->info('Registered service "' . $definition['Name'] . '" (id=' . $definition['ID'] . ') with Consul at ' . $client->baseUri());

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Consul registration failed: ' . $e->getMessage());
            logger()->error('Consul registration failed', [
                'service' => $definition['Name'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return self::FAILURE;
        }
    }
}
