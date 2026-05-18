<?php declare(strict_types = 1);

namespace Egst\EmorfiqScheduler\Bridge\SymfonyLock;

use Egst\EmorfiqScheduler\LockHandle;
use Egst\EmorfiqScheduler\LockHandleState;
use Egst\EmorfiqScheduler\LockProvider;
use Override;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\Exception\LockReleasingException;
use Symfony\Component\Lock\Key;
use Symfony\Component\Lock\PersistingStoreInterface;

/**
 * An adapter implementing Symfony PersistingStore over the internal SharedStore abstraction.
 */
final readonly class SymfonyStore implements PersistingStoreInterface {

    /**
     * @param float $initialTtl Small implementation detail: Symfony backends implement checks for maximum lock delay.
     *                          To use the Symfony backends with a Symfony frontend (crunz), this number should be
     *                          ideally set to the same delay configured in the selected backend.
     *                          All the provided backends default to 300.
     */
    public function __construct (
        private LockProvider $provider,
        private float $initialTtl = 300.
    ) {}

    #[Override]
    public function save (Key $key): void {
        $resource = (string) $key;
        $key->reduceLifetime($this->initialTtl);
        $handle   = $this->provider->acquire($resource)
            ?? throw new LockConflictedException('Failed to acquire a lock.');
        $this->setHandle($key, $handle);
    }

    #[Override]
    public function delete (Key $key): void {
        $handle = $this->getHandle($key);
        if ($handle === null || !$handle->release())
            throw new LockReleasingException('Failed to release a lock.');
    }

    #[Override]
    public function exists (Key $key): bool {
        $handle = $this->getHandle($key);
        return $handle !== null && $handle->acquired();
    }

    #[Override]
    public function putOffExpiration (Key $key, float $ttl): void {
        $handle = $this->getHandle($key);
        if ($handle === null || $handle->refresh($ttl))
            throw new LockConflictedException('Failed to refresh a lock.');
    }

    private function setHandle (Key $key, LockHandle $handle): void {
        $key->setState(self::class, $handle->snapshot());
    }

    private function getHandle (Key $key): ?LockHandle {
        $resource = (string) $key;
        $snapshot = $key->getState(self::class);
        if (!$snapshot instanceof LockHandleState)
            return null;
        return $this->provider->restore($resource, $snapshot);
    }

}
