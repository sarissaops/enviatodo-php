<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Exception;

use SarissaOps\Enviatodo\Exception;

final class HttpServerException extends \RuntimeException implements Exception
{
    public static function serverError(int $code = 500): self
    {
        return new self('The server returned an error.', $code);
    }

    public static function networkError(\Throwable $previous): self
    {
        return new self('A network error occurred: ' . $previous->getMessage(), 0, $previous);
    }

    public static function unknownHttpResponseCode(int $code): self
    {
        return new self(sprintf('The server returned an unknown HTTP response code: %d.', $code), $code);
    }
}
