<?php declare(strict_types = 1);

namespace Egst\EmorfiqScheduler\Locking\SymfonyLock;

use Egst\EmorfiqScheduler\LockHandleState;
use Egst\EmorfiqScheduler\LockProvider;
use Override;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\Key;
use Symfony\Component\Lock\PersistingStoreInterface;

/**
 * A lock provider adapter for the Symfony PersistingStoreInterface backends.
 *
 * This lock provider uses our LockHandle interface to store the Symfony's Key object
 * so that the underlying backend can use it for its implementation-defined state.
 */
final readonly class SymfonyLockProvider implements LockProvider {

    public function __construct (
        private PersistingStoreInterface $store
    ) {}

    #[Override]
    public function restore (string $resource, LockHandleState $snapshot): ?SymfonyLockHandle {
        $key = $snapshot->get('key');
        return $key instanceof Key
            ? new SymfonyLockHandle($resource, $key, $this->store)
            : null;
    }

    #[Override]
    public function acquire (string $resource): ?SymfonyLockHandle {
        $key = new Key($resource);
        try {
            $this->store->save($key);
        } catch (LockConflictedException) {
            return null;
        }
        return new SymfonyLockHandle($resource, $key, $this->store);
    }

}
