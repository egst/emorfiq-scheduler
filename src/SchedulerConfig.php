<?php declare(strict_types = 1);

namespace Egst\EmorfiqScheduler;

use DateTimeZone;
use Egst\EmorfiqScheduler\Store\SharedStore;

/**
 * Common configuration without any library-specific dependencies.
 */
final readonly class SchedulerConfig {

    /**
     * @param ?SharedStore  $sharedLocking A locking backend without ownership semantics.
     *                                     The Laravel library is only able to use this type of locking backend.
     * @param ?LockProvider $ownedLocking  A locking backend with ownership semantics.
     *                                     The Crunz library is only able to use this type of locking backend.
     *                                     You can use Symfony Lock component PersistingStoreInterface implementations
     *                                     via the SymfonyLockProvider adapter.
     */
    public function __construct (
        public DateTimeZone  $timeZone,
        public ?SharedStore  $sharedLocking = null,
        public ?LockProvider $ownedLocking  = null,
    ) {}

}
