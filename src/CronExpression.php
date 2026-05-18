<?php declare(strict_types = 1);

namespace Egst\EmorfiqScheduler;

use Stringable;
use InvalidArgumentException;

final readonly class CronExpression implements Stringable {
    public function __construct (
        private string $expression
    ) {
        if (!preg_match('/^(\S+\s+){4}\S+$/', $expression)) {
            throw new InvalidArgumentException("Invalid cron expression: $expression");
        }
    }

    public function __toString (): string {
        return $this->expression;
    }
}
