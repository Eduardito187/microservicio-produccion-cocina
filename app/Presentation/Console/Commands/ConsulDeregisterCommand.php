<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Console\Commands;

use App\Infrastructure\ServiceDiscovery\ConsulClient;
use Illuminate\Console\Command;

class ConsulDeregisterCommand extends Command
{
    protected $signature = 'consul:deregister';

    protected $description = 'Deregister this service from Consul';

    public function handle(): int
    {
        $config = (array) config('service-discovery');
        $consulConfig = (array) ($config['consul'] ?? []);
        $host = (string) ($consulConfig['host'] ?? '');
        if ($host === '') {
            $this->warn('CONSUL_HOST is empty; nothing to do.');

            return self::SUCCESS;
        }

        $serviceId = (string) ($config['service_id'] ?? $config['service_name'] ?? '');
        if ($serviceId === '') {
            $this->error('SERVICE_ID / SERVICE_NAME missing');

            return self::FAILURE;
        }

        try {
            (new ConsulClient($consulConfig))->deregister($serviceId);
            $this->info('Deregistered ' . $serviceId . ' from Consul.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Consul deregistration failed: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
