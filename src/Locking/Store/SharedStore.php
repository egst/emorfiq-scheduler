<?php declare(strict_types = 1);

namespace Egst\EmorfiqScheduler\Locking\Store;

/**
 * A lock store with no ownership. Anyone can manage anyone's lock.
 * A store with such semantics is necessary for the Laravel scheduler.
 *
 * This store interface does not need the LockProvider+LockHandle abstraction
 * since it doesn't really make sense to hold any handle with no ownership
 * and Laravel expects a procedural interface with no handle objects anyway.
 */
interface SharedStore {

    public function create (string $resource, ?float $ttl = null): bool;

    public function exists (string $resource): bool;

    public function remove (string $resource): bool;

}
