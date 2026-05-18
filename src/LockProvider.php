<?php declare(strict_types = 1);

namespace Egst\EmorfiqScheduler;

/**
 * Lock provider acquires a lock and creates a lock handle object
 * for any further management of the lock's state and ownership.
 *
 * This kind of an OOP handle interface introduces more complexity than
 * a simple procedural lock repository, but it provides several advantages:
 * - It is easily extensible
 * - It allows implementations with a wide variety of locking backends with lock ownership:
 *     - backends that rely on internal state or connection to handle ownership
 *     - backends that require the caller to hold a handle to handle ownership
 *     - even backends that use an opaque handle object with implementation-defined state
 *
 * To be able to pass the necessary handle data to a third-party handle object's
 * state (e.g. the Symfony Key) and still keep it serializable, the lock provider
 * allows restoring the handle from its snapshot which contains just the necessary
 * ownership data (e.g. a token).
 *
 * Note: Laravel is not intended to be used with owner-aware locking so this whole
 * abstraction is mostly for Crunz or other libraries that support this kind of
 * locking. If we only need custom locking backends for Laravel, we can keep just
 * the SharedStore abstraction and everything else can be removed.
 */
interface LockProvider {

    public function restore (string $resource, LockHandleState $snapshot): ?LockHandle;

    public function acquire (string $resource): ?LockHandle;

}
