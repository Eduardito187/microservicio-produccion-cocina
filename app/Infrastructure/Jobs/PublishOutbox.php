<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Jobs;

use App\Infrastructure\Persistence\Model\EventStore;
use App\Infrastructure\Persistence\Model\Outbox;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use App\Application\Shared\BusInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Str;
use DateTimeImmutable;

/**
 * @class PublishOutbox
 * @package App\Infrastructure\Jobs
 */
class PublishOutbox implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * Constructor
     */
    public function __construct() {}

    /**
     * @param BusInterface $bus
     * @return void
     */
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

            logger()->info('Outbox claimed', [
                'claim_id' => $claimId,
                'count' => count($ids),
            ]);

            return $ids;
        });

        if ($claimedIds === []) {
            logger()->info('Outbox empty', ['claim_id' => $claimId]);
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

                    logger()->info('Outbox publishing', [
                        'event_id' => $row->event_id,
                        'event_name' => $row->event_name,
                        'aggregate_id' => $row->aggregate_id,
                        'schema_version' => $row->schema_version,
                        'correlation_id' => $row->correlation_id,
                        'payload' => $row->payload,
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

                    logger()->info('Outbox published', [
                        'event_id' => $row->event_id,
                        'event_name' => $row->event_name,
                        'aggregate_id' => $row->aggregate_id,
                    ]);
                } catch (\Throwable $e) {
                    logger()->error('Outbox publish failed', [
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
