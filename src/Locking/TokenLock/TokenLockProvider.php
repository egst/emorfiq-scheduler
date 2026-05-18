<?php declare(strict_types = 1);

namespace Egst\EmorfiqScheduler\Locking\TokenLock;

use Egst\EmorfiqScheduler\LockHandleState;
use Egst\EmorfiqScheduler\Locking\Store\TokenStore;
use Egst\EmorfiqScheduler\LockProvider;
use Override;

/**
 * A lock provider representing a locking backend with token-based ownership.
 *
 * The corresponding lock handle holds the ownership token.
 */
final readonly class TokenLockProvider implements LockProvider {

    public function __construct (
        private TokenStore $store
    ) {}

    #[Override]
    public function restore (string $resource, LockHandleState $snapshot): ?TokenLockHandle {
        $token = $snapshot->get('token');
        return is_string($token)
            ? new TokenLockHandle($resource, $token, $this->store)
            : null;
    }

    #[Override]
    public function acquire (string $resource): ?TokenLockHandle {
        $token = $this->newToken();
        return $this->store->acquire($resource, $token)
            ? new TokenLockHandle($resource, $token, $this->store)
            : null;
    }

    private function newToken (): string {
        return bin2hex(random_bytes(16));
    }

}
