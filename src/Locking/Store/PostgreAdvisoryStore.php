<?php declare(strict_types = 1);

namespace Egst\EmorfiqScheduler\Locking\Store;

use Override;
use PDO;

final readonly class PostgreAdvisoryStore implements SessionStore {

    public function __construct (
        private PDO $pdo
    ) {}

    #[Override]
    public function acquire (string $resource, ?float $ttl = null): bool {
        # TODO
        return false;
    }

    #[Override]
    public function acquired (string $resource): bool {
        # TODO
        return false;
    }

    #[Override]
    public function release (string $resource): bool {
        # TODO
        return false;
    }

    #[Override]
    public function refresh (string $resource, float $ttl): bool {
        // PostgreSQL Advisory locks don't have any built-it TTL. They don't expire until the connection is closed.
        return true;
    }

}
