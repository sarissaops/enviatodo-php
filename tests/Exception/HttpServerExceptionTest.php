<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\Exception;

use PHPUnit\Framework\TestCase;
use SarissaOps\Enviatodo\Exception\HttpServerException;

final class HttpServerExceptionTest extends TestCase
{
    public function testServerError(): void
    {
        $e = HttpServerException::serverError(503);

        $this->assertSame(503, $e->getCode());
    }

    public function testNetworkErrorPreservesPrevious(): void
    {
        $previous = new \RuntimeException('connection reset');

        $e = HttpServerException::networkError($previous);

        $this->assertSame($previous, $e->getPrevious());
    }

    public function testUnknownHttpResponseCode(): void
    {
        $e = HttpServerException::unknownHttpResponseCode(599);

        $this->assertSame(599, $e->getCode());
    }
}
