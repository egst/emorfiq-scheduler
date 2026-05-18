<?php declare(strict_types = 1);

namespace Egst\EmorfiqScheduler\Exception;

use LogicException;

/**
 * Thrown in an unsuccessful attempt to serialize the lock handle.
 *
 * If a third-party library uses some opaque handle object (e.g. Symfony's Key)
 * to hold implementation-defined state, we might utilize it to store our handle
 * data that handles lock ownership. If the library attempts to pass its handle
 * object to another process, it needs to be serialized. Since some locking
 * backends (e.g. postgre advisory locks) don't support lock handle
 * serialization, this can fail.
 */
class LockHandleNotSerializable extends LogicException {}
