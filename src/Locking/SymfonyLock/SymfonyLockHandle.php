<?php declare(strict_types = 1);

namespace Egst\EmorfiqScheduler\Locking\SymfonyLock;

use Egst\EmorfiqScheduler\LockHandle;
use Egst\EmorfiqScheduler\LockHandleState;
use Override;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\Exception\LockReleasingException;
use Symfony\Component\Lock\Key;
use Symfony\Component\Lock\PersistingStoreInterface;

/**
 * A lock handle adapter for the Symfony PersistingStoreInterface backends.
 *
 * This handle holds Symfony's Key object. It can be used by the underlying
 * backend to store its implementation-defined state into it as it normally would.
 * This could hold a token or it could represent session-based ownership or
 * anything the given Symfony backend implements.
 * 
 * It can only be serialized when the underlying Symfony Key is serializable.
 *
 * The Symfony backend's exceptions are discarded. A possible improvement could be
 * to log these exceptions or to extend the LockHandle interface to return a more
 * complex "result" object.
 */
final readonly class SymfonyLockHandle implements LockHandle {

    public function __construct (
        private string                   $resource,
        private Key                      $key,
        private PersistingStoreInterface $store,
    ) {}

    #[Override]
    public function resource (): string {
        return $this->resource;
    }

    #[Override]
    public function snapshot (): LockHandleState {
        return LockHandleState::serializable([
            'key' => $this->key,
        ]);
    }

    #[Override]
    public function acquired (): bool {
        return $this->store->exists($this->key);
    }

    #[Override]
    public function release (): bool {
        try {
            $this->store->delete($this->key);
            return true;
        } catch (LockReleasingException) {
            return false;
        }
    }

    #[Override]
    public function refresh (float $ttl): bool {
        try {
            $this->store->putOffExpiration($this->key, $ttl);
            return true;
        } catch (LockConflictedException) {
            return false;
        }
    }

}
