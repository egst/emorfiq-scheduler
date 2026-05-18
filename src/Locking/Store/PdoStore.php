<?php declare(strict_types = 1);

namespace Egst\EmorfiqScheduler\Locking\Store;

use PDO;

final readonly class PdoStore implements SharedStore, TokenStore {

    public function __construct (
        private PDO $pdo
    ) {}

    public function create (string $resource, ?float $ttl = null): bool {
        # TODO
        return false;
    }

    public function exists (string $resource): bool {
        # TODO
        return false;
    }

    public function remove (string $resource): bool {
        # TODO
        return false;
    }

    public function acquire (string $resource, string $token, ?float $ttl = null): bool {
        # TODO
        return false;
    }

    public function acquired (string $resource, string $token): bool {
        # TODO
        return false;
    }

    public function release (string $resource, string $token): bool {
        # TODO
        return false;
    }

    public function refresh (string $resource, string $token, float $ttl): bool {
        # TODO
        return false;
    }

}
