<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Model\InboundEvent as InboundEventModel;
use App\Domain\Produccion\Repository\InboundEventRepositoryInterface;
use App\Domain\Produccion\Entity\InboundEvent;

class InboundEventRepository implements InboundEventRepositoryInterface
{
    /**
     * @param string $eventId
     * @return bool
     */
    public function existsByEventId(string $eventId): bool
    {
        return InboundEventModel::query()->where('event_id', $eventId)->exists();
    }

    /**
     * @param InboundEvent $event
     * @return int
     */
    public function save(InboundEvent $event): int
    {
        $model = InboundEventModel::query()->create([
            'event_id' => $event->eventId,
            'event_name' => $event->eventName,
            'occurred_on' => $event->occurredOn,
            'payload' => $event->payload,
        ]);

        return $model->id;
    }
}
