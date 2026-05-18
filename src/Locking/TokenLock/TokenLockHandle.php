<?php declare(strict_types = 1);

namespace Egst\EmorfiqScheduler\Locking\TokenLock;

use Egst\EmorfiqScheduler\LockHandle;
use Egst\EmorfiqScheduler\LockHandleState;
use Egst\EmorfiqScheduler\Locking\Store\TokenStore;
use Override;

/**
 * A lock handle representing token-based ownership of a lock.
 *
 * The lock handle holds the ownership token.
 *
 * This lock handle is serializable, since the just
 * the string token is needed to represent the ownership.
 */
final readonly class TokenLockHandle implements LockHandle {

    public function __construct (
        private string     $resource,
        private string     $token,
        private TokenStore $store,
    ) {}

    #[Override]
    public function resource (): string {
        return $this->resource;
    }

    #[Override]
    public function snapshot (): LockHandleState {
        return LockHandleState::serializable([
            'token' => $this->token,
        ]);
    }

    #[Override]
    public function acquired (): bool {
        return $this->store->acquired($this->resource, $this->token);
    }

    #[Override]
    public function release (): bool {
        return $this->store->release($this->resource, $this->token);
    }

    #[Override]
    public function refresh (float $ttl): bool {
        return $this->store->refresh($this->resource, $this->token, $ttl);
    }

}
