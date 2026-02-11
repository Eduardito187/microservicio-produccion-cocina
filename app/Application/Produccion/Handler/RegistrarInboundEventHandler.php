<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\InboundEventRepositoryInterface;
use App\Application\Produccion\Command\RegistrarInboundEvent;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\InboundEvent;
use Illuminate\Database\QueryException;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Illuminate\Support\Str;
use Psr\Log\NullLogger;

/**
 * @class RegistrarInboundEventHandler
 * @package App\Application\Produccion\Handler
 */
class RegistrarInboundEventHandler
{
    /**
     * @var InboundEventRepositoryInterface
     */
    private $inboundEventRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param InboundEventRepositoryInterface $inboundEventRepository
     * @param TransactionAggregate $transactionAggregate
     * @param ?LoggerInterface $logger
     */
    public function __construct(
        InboundEventRepositoryInterface $inboundEventRepository,
        TransactionAggregate $transactionAggregate,
        ?LoggerInterface $logger = null
    ) {
        $this->inboundEventRepository = $inboundEventRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param RegistrarInboundEvent $command
     * @return bool
     */
    public function __invoke(RegistrarInboundEvent $command): bool
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): bool {
            $schemaVersion = $this->resolveSchemaVersion($command->schemaVersion);
            $correlationId = $command->correlationId ?: (string) Str::uuid();

            $event = new InboundEvent(
                null,
                $command->eventId,
                $command->eventName,
                $command->occurredOn,
                $command->payload,
                $schemaVersion,
                $correlationId
            );

            try {
                $this->inboundEventRepository->save($event);
            } catch (QueryException $e) {
                if ($this->isDuplicateKey($e)) {
                    $this->logger->info('Inbound event duplicate', [
                        'event_id' => $command->eventId,
                        'event_name' => $command->eventName,
                        'correlation_id' => $correlationId,
                    ]);
                    return true;
                }
                $this->logger->error('Inbound event insert failed', [
                    'event_id' => $command->eventId,
                    'event_name' => $command->eventName,
                    'correlation_id' => $correlationId,
                    'error' => $e->getMessage(),
                    'exception' => $e,
                ]);
                throw $e;
            }

            return false;
        });
    }

    /**
     * @param QueryException $e
     * @return bool
     */
    private function isDuplicateKey(QueryException $e): bool
    {
        $errorInfo = $e->errorInfo ?? null;
        if (!is_array($errorInfo) || !isset($errorInfo[1]) || (int) $errorInfo[1] !== 1062) {
            return false;
        }

        $message = $e->getMessage();
        if (!is_string($message)) {
            return false;
        }

        return str_contains($message, 'inbound_events_event_id_unique')
            || str_contains($message, 'event_id');
    }

    /**
     * @param int|null $schemaVersion
     * @return int
     */
    private function resolveSchemaVersion(?int $schemaVersion): int
    {
        if ($schemaVersion === null) {
            throw new InvalidArgumentException('schema_version is required');
        }
        $supported = '1';
        if (function_exists('config')) {
            try {
                $supported = config('rabbitmq.inbound.schema_versions', '1');
            } catch (\Throwable $e) {
                $supported = '1';
            }
        }
        $supportedList = array_filter(array_map('trim', explode(',', (string) $supported)));
        $supportedInts = array_map('intval', $supportedList);
        if ($supportedInts === []) {
            $supportedInts = [1];
        }

        $version = $schemaVersion ?? $supportedInts[0];
        if (!in_array($version, $supportedInts, true)) {
            throw new InvalidArgumentException('Unsupported schema_version: ' . $version);
        }

        return $version;
    }
}
