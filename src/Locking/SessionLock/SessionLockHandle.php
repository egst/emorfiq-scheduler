<?php declare(strict_types = 1);

namespace Egst\EmorfiqScheduler\Locking\SessionLock;

use Egst\EmorfiqScheduler\LockHandle;
use Egst\EmorfiqScheduler\LockHandleState;
use Egst\EmorfiqScheduler\Locking\Store\SessionStore;
use Override;

/**
 * A lock handle representing session-based ownership of a lock.
 *
 * When the session ends (e.g. a DB connection), the lock is released. There is no
 * token to store. Only the connection held by the store determines the ownership.
 *
 * This lock handle is not serializable, since passing it to another process would
 * mean loosing the connection.
 */
final readonly class SessionLockHandle implements LockHandle {

    public function __construct (
        private string       $resource,
        private SessionStore $store,
    ) {}

    #[Override]
    public function resource (): string {
        return $this->resource;
    }

    #[Override]
    public function snapshot (): LockHandleState {
        return LockHandleState::unserializable();
    }

    #[Override]
    public function acquired (): bool {
        return $this->store->acquired($this->resource);
    }

    #[Override]
    public function release (): bool {
        return $this->store->release($this->resource);
    }

    #[Override]
    public function refresh (float $ttl): bool {
        return $this->store->refresh($this->resource, $ttl);
    }

}
