<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Exception;

use SarissaOps\Enviatodo\Exception;

/**
 * Base exception: transport-level failures aside, this is thrown when the
 * Enviatodo envelope itself reports an error (`error: true`), even on HTTP 200.
 */
class EnviatodoException extends \RuntimeException implements Exception {}
