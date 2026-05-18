<?php declare(strict_types = 1);

namespace Egst\EmorfiqScheduler\Locking\SessionLock;

use Egst\EmorfiqScheduler\LockHandle;
use Egst\EmorfiqScheduler\LockHandleState;
use Egst\EmorfiqScheduler\Locking\Store\SessionStore;
use Egst\EmorfiqScheduler\LockProvider;
use Override;

/**
 * A lock provider representing a locking backend with session-based ownership.
 *
 * When the session ends (e.g. a DB connection), the lock is released.
 * The connection is held by the underlying store.
 */
final readonly class SessionLockProvider implements LockProvider {

    public function __construct (
        private SessionStore $store
    ) {}

    #[Override]
    public function restore (string $resource, LockHandleState $snapshot): LockHandle {
        return new SessionLockHandle($resource, $this->store);
    }

    #[Override]
    public function acquire (string $resource): ?SessionLockHandle {
        return $this->store->acquire($resource)
            ? new SessionLockHandle($resource, $this->store)
            : null;
    }

}

