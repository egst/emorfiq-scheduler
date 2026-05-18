<?php declare(strict_types = 1);

namespace Egst\EmorfiqScheduler\Locking\Store;

/**
 * A lock store with session-based ownership.
 * The session connection is implicitly tied to the existance of this store.
 * Anyone who holds a reference to this store can access the same locks.
 *
 * This is the lowest level of this locking abstraction.
 * Most simple locking backends can be represented by one of the store interfaces.
 *
 * This store can be transformed into the LockProvider+LockHandle interface
 * using the SessionLockProvider and SessionLockHandle adapters.
 *
 * For more complex backends (e.g. a third-party handle-based interface),
 * implement the LockProvider and LockHandle interfaces directly.
 */
interface SessionStore {

    public function acquire (string $resource): bool;

    public function acquired (string $resource): bool;

    public function refresh (string $resource, float $ttl): bool;

    public function release (string $resource): bool;

}
