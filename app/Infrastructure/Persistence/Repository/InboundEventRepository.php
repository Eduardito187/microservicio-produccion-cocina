<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Model\InboundEvent as InboundEventModel;
use App\Domain\Produccion\Repository\InboundEventRepositoryInterface;
use App\Domain\Shared\Exception\DuplicateRecordException;
use App\Domain\Produccion\Entity\InboundEvent;

/**
 * @class InboundEventRepository
 * @package App\Infrastructure\Persistence\Repository
 */
class InboundEventRepository implements InboundEventRepositoryInterface
{
    /**
     * @param InboundEvent $event
     * @return int
     */
    public function save(InboundEvent $event): string
    {
        try {
            $model = InboundEventModel::query()->create([
                'event_id' => $event->eventId,
                'event_name' => $event->eventName,
                'occurred_on' => $event->occurredOn,
                'payload' => $event->payload,
                'schema_version' => $event->schemaVersion,
                'correlation_id' => $event->correlationId,
            ]);
        } catch (\Throwable $exception) {
            $message = $exception->getMessage();
            if (
                str_contains($message, 'Duplicate entry')
                || str_contains($message, 'UNIQUE constraint failed')
                || str_contains($message, 'inbound_events_event_id_unique')
            ) {
                throw new DuplicateRecordException($message, previous: $exception);
            }
            throw $exception;
        }

        return $model->id;
    }
}
