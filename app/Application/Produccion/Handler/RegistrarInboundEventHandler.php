<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\InboundEventRepositoryInterface;
use App\Application\Produccion\Command\RegistrarInboundEvent;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\InboundEvent;
use App\Domain\Shared\Exception\DuplicateRecordException;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
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
     * @var string
     */
    private $supportedSchemaVersions;

    /**
     * Constructor
     *
     * @param InboundEventRepositoryInterface $inboundEventRepository
     * @param TransactionAggregate $transactionAggregate
     * @param ?LoggerInterface $logger
     * @param string $supportedSchemaVersions
     */
    public function __construct(
        InboundEventRepositoryInterface $inboundEventRepository,
        TransactionAggregate $transactionAggregate,
        ?LoggerInterface $logger = null,
        string $supportedSchemaVersions = '1'
    ) {
        $this->inboundEventRepository = $inboundEventRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->logger = $logger ?? new NullLogger();
        $this->supportedSchemaVersions = $supportedSchemaVersions;
    }

    /**
     * @param RegistrarInboundEvent $command
     * @return bool
     */
    public function __invoke(RegistrarInboundEvent $command): bool
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): bool {
            $schemaVersion = $this->resolveSchemaVersion($command->schemaVersion);
            $correlationId = $command->correlationId ?: $this->generateUuidV4();

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
            } catch (DuplicateRecordException $exception) {
                $this->logger->info('Inbound event duplicate', [
                    'event_id' => $command->eventId,
                    'event_name' => $command->eventName,
                    'correlation_id' => $correlationId,
                ]);
                return true;
            } catch (\Throwable $exception) {
                $this->logger->error('Inbound event insert failed', [
                    'event_id' => $command->eventId,
                    'event_name' => $command->eventName,
                    'correlation_id' => $correlationId,
                    'error' => $exception->getMessage(),
                    'exception' => $exception,
                ]);
                throw $exception;
            }

            return false;
        });
    }

    /**
     * @return string
     */
    private function generateUuidV4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);
        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
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
        $supportedList = array_filter(array_map('trim', explode(',', $this->supportedSchemaVersions)));
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
