<?php declare(strict_types = 1);

namespace Egst\EmorfiqScheduler\Locking\Store;

/**
 * A lock store with token-based ownership.
 * Anyone who holds the token can manage the corresponding locks.
 *
 * This is the lowest level of this locking abstraction.
 * Most simple locking backends can be represented by one of the store interfaces.
 *
 * This store can be transformed into the LockProvider+LockHandle interface
 * using the TokenLockProvider and TokenLockHandle adapters.
 *
 * For more complex backends (e.g. a third-party handle-based interface),
 * implement the LockProvider and LockHandle interfaces directly.
 */
interface TokenStore {

    public function acquire (string $resource, string $token): bool;

    public function acquired (string $resource, string $token): bool;

    public function refresh (string $resource, string $token, float $ttl): bool;

    public function release (string $resource, string $token): bool;

}
