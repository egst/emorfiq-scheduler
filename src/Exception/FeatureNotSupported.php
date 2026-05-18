<?php declare(strict_types = 1);

namespace Egst\EmorfiqScheduler\Exception;

use LogicException;

/**
 * Thrown when a requested feature is not supported by the selected adapters.
 */
class FeatureNotSupported extends LogicException {}
