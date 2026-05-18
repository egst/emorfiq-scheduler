<?php declare(strict_types = 1);

namespace Egst\EmorfiqScheduler\Locking\Store;

use Override;
use PDO;

final readonly class PdoStore implements SharedStore, TokenStore {

    public function __construct (
        private PDO $pdo
    ) {}

    #[Override]
    public function create (string $resource, ?float $ttl = null): bool {
        # TODO
        return false;
    }

    #[Override]
    public function exists (string $resource): bool {
        # TODO
        return false;
    }

    #[Override]
    public function remove (string $resource): bool {
        # TODO
        return false;
    }

    #[Override]
    public function acquire (string $resource, string $token, ?float $ttl = null): bool {
        # TODO
        return false;
    }

    #[Override]
    public function acquired (string $resource, string $token): bool {
        # TODO
        return false;
    }

    #[Override]
    public function release (string $resource, string $token): bool {
        # TODO
        return false;
    }

    #[Override]
    public function refresh (string $resource, string $token, float $ttl): bool {
        # TODO
        return false;
    }

}
