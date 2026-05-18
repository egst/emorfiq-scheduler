<?php declare(strict_types = 1);

namespace Egst\EmorfiqScheduler;

/**
 * A lock handle object is intended to be held by the caller to handle ownership of the locks.
 *
 * This handle can contain an owner token or any other mechanism needed for the
 * backend. Some backends rely on session-based ownership where just the
 * referenced connection (e.g. a database connection) defines the ownership. For
 * these backends, the handle doesn't need to contain any additional data.
 *
 * The handle will usually conatin a reference to the underlying locking store to
 * be able to perform the necessary operations.
 *
 * To store the lock handle in a third-party handle object, a "snapshot" of the
 * ownership data can be taken to be later reconstructed back into the lock handle
 * by the lock provider. Token-based backends can pass the owner token in the
 * snapshot state, while session-based backends can simply pass an empty snapshot.
 * The lock provider also references the underlying store, so it can fully
 * reconstruct the lock handle. The snapshot can be serialized, in case the
 * scheduling library (or our custom locking in the future) needs to pass the lock
 * to another process. Session-based handles will throw when serialized, since the
 * session will not survive the process boundary.
 */
interface LockHandle {

    public function resource (): string;

    public function snapshot (): LockHandleState;

    public function acquired (): bool;

    public function release (): bool;

    public function refresh (float $ttl): bool;

}
