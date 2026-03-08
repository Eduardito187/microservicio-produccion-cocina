<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Jobs;

use App\Application\Shared\BusInterface;
use App\Infrastructure\Persistence\Model\EventStore;
use App\Infrastructure\Persistence\Model\Outbox;
use DateTimeImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * @class PublishOutbox
 */
class PublishOutbox implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;
    private const OUTBOUND_LOG_CHANNEL = 'rabbit_audit_outbound';

    /**
     * Constructor
     */
    public function __construct() {}

    public function handle(BusInterface $bus): void
    {
        $claimId = (string) Str::uuid();
        $now = Carbon::now();
        $lockExpiry = $now->copy()->subMinutes(5);

        $claimedIds = DB::transaction(function () use ($claimId, $now, $lockExpiry): array {
            $ids = Outbox::query()
                ->whereNull('published_at')
                ->where(function ($query) use ($lockExpiry) {
                    $query->whereNull('locked_at')
                        ->orWhere('locked_at', '<', $lockExpiry);
                })
                ->orderBy('occurred_on')
                ->limit(100)
                ->pluck('id')
                ->all();

            if ($ids === []) {
                return [];
            }

            Outbox::query()
                ->whereIn('id', $ids)
                ->whereNull('published_at')
                ->where(function ($query) use ($lockExpiry) {
                    $query->whereNull('locked_at')
                        ->orWhere('locked_at', '<', $lockExpiry);
                })
                ->update([
                    'locked_at' => $now,
                    'locked_by' => $claimId,
                ]);

            logger()->info('Outbox tomado', [
                'claim_id' => $claimId,
                'count' => count($ids),
            ]);
            Log::channel(self::OUTBOUND_LOG_CHANNEL)->info('Lote saliente Rabbit', [
                'claim_id' => $claimId,
                'pending' => count($ids),
                'exchange' => (string) config('rabbitmq.exchange', ''),
            ]);

            return $ids;
        });

        if ($claimedIds === []) {
            logger()->info('Outbox vacio', ['claim_id' => $claimId]);

            return;
        }

        Outbox::query()
            ->whereIn('id', $claimedIds)
            ->where('locked_by', $claimId)
            ->orderBy('occurred_on')
            ->get()
            ->each(function (Outbox $row) use ($bus, $now): void {
                try {
                    EventStore::query()->firstOrCreate(
                        ['event_id' => $row->event_id],
                        [
                            'event_name' => $row->event_name,
                            'aggregate_id' => $row->aggregate_id,
                            'payload' => $row->payload,
                            'occurred_on' => $row->occurred_on,
                            'schema_version' => $row->schema_version,
                            'correlation_id' => $row->correlation_id,
                        ]
                    );

                    logger()->info('Publicando outbox', [
                        'event_id' => $row->event_id,
                        'event_name' => $row->event_name,
                        'aggregate_id' => $row->aggregate_id,
                        'schema_version' => $row->schema_version,
                        'correlation_id' => $row->correlation_id,
                        'payload' => $row->payload,
                    ]);
                    Log::channel(self::OUTBOUND_LOG_CHANNEL)->info('Evento encolado de salida Rabbit', [
                        'event' => $row->event_name,
                        'event_id' => $row->event_id,
                        'aggregate_id' => $row->aggregate_id,
                        'correlation_id' => $row->correlation_id,
                        'schema_version' => $row->schema_version,
                    ]);

                    $bus->publish(
                        $row->event_id,
                        $row->event_name,
                        $row->payload,
                        new DateTimeImmutable($row->occurred_on->format(DATE_ATOM)),
                        [
                            'aggregate_id' => $row->aggregate_id,
                            'correlation_id' => $row->correlation_id,
                            'schema_version' => $row->schema_version,
                        ]
                    );

                    $row->forceFill([
                        'published_at' => $now,
                        'locked_at' => null,
                        'locked_by' => null,
                    ])->save();

                    logger()->info('Outbox publicado', [
                        'event_id' => $row->event_id,
                        'event_name' => $row->event_name,
                        'aggregate_id' => $row->aggregate_id,
                    ]);
                } catch (\Throwable $e) {
                    logger()->error('Error al publicar outbox', [
                        'event_id' => $row->event_id,
                        'event_name' => $row->event_name,
                        'aggregate_id' => $row->aggregate_id,
                        'correlation_id' => $row->correlation_id,
                        'payload' => $row->payload,
                    ]);
                }
            });
    }
}
