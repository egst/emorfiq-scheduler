<?php declare(strict_types = 1);

namespace Egst\EmorfiqScheduler\Bridge\LaravelScheduler;

use DateTimeInterface;
use Egst\EmorfiqScheduler\Locking\Store\SharedStore;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\EventMutex;
use Illuminate\Console\Scheduling\SchedulingMutex;
use Override;

/**
 * An adapter implementing Laravel EventMutex and SchedulingMutex over the internal SharedStore abstraction.
 * The distinction is made by the presence of the $time parameter, which is only used for ovelap locks.
 *
 * Disadvantages and possible immprovements:
 *
 * Laravel scheduler expects "shared" locking semantics from its mutex interface.
 * This approach can fail if the current process runs past its TTL, which is the
 * main disadvantage of Laravel's locking mechanism. Unfortunately there's no
 * straightforward way to fix this without any hacks that would break scheduler
 * utilities (like schedule list and cache clear) or background jobs.
 *
 * If these tradeoffs are acceptable, it could be possible to implement an adapter
 * that forces ownership semantics into this interface via tokens stored in memory
 * or session-based ownership. Such an adapter could be built with both
 * LockProvider and SharedStore with the same underlyin gstorage and expose
 * ownership locking on create and forget but shared locks on exists.
 */
final readonly class LaravelMutex implements EventMutex, SchedulingMutex {

    const float MINUTE_LOCK_TTL = 3600.;

    public function __construct (
        private SharedStore $store,
    ) {}

    #[Override]
    public function create (Event $event, ?DateTimeInterface $time = null): bool {
        $resource = $this->resource($event, $time);
        $ttl = $time !== null
            ? self::MINUTE_LOCK_TTL   // For the minute lock
            : $event->expiresAt * 60; // For the overlap lock
        return $this->store->create($resource, $ttl);
    }

    #[Override]
    public function exists (Event $event, ?DateTimeInterface $time = null): bool {
        $resource = $this->resource($event, $time);
        return $this->store->exists($resource);
    }

    #[Override]
    public function forget (Event $event): bool {
        $resource = $this->resource($event, null);
        return $this->store->remove($resource);
    }

    private function resource (Event $event, ?DateTimeInterface $time): string {
        return $time !== null
            ? $event->mutexName() . '/' . $time->format('Hi')
            : $event->mutexName();
    }

}
