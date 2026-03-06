<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Console\Commands;

use App\Infrastructure\Jobs\PublishOutbox;
use Illuminate\Console\Command;

/**
 * @class PublishOutboxCommand
 * @package App\Presentation\Console\Commands
 */
class PublishOutboxCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'outbox:publish';

    /**
     * @var string
     */
    protected $description = 'Publica eventos pendientes del outbox hacia el bus configurado';

    /**
     * @return int
     */
    public function handle(): int
    {
        PublishOutbox::dispatchSync();
        $this->info('Outbox procesado.');
        return self::SUCCESS;
    }
}

