<?php declare(strict_types = 1);

namespace Egst\EmorfiqScheduler;

use Egst\EmorfiqScheduler\Exception\LockHandleNotSerializable;

/**
 * A serializable snapshot of a lock handle's state that preserves the necessary ownership data.
 *
 * Session-based handles are unserializable and will throw when serialized since
 * the session will not survive the process boundary. Token-based handles can
 * simply pass the owner token and later be reconstructed by the lock provider.
 */
class LockHandleState {

    /** @param array<string, mixed> $values */
    private function __construct (
        private array $values,
        private bool $serializable,
    ) {}

    /** @param array<string, mixed> $values */
    static function serializable (array $values = []): self {
        return new self($values, serializable: true);
    }

    /** @param array<string, mixed> $values */
    static function unserializable (array $values = []): self {
        return new self($values, serializable: false);
    }

    public function get (string $key): mixed {
        return $this->values[$key] ?? null;
    }

    public function __serialize (): string {
        return $this->serializable
            ? serialize($this->values)
            : throw new LockHandleNotSerializable;
    }

    public function __unserialize (string $data): void {
        $this->values = unserialize($data);
        $this->serializable = true;
    }

}
