<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\Exception;

use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use SarissaOps\Enviatodo\Exception\HttpClientException;

final class HttpClientExceptionTest extends TestCase
{
    public function testBadRequestUsesEnvelopeMessage(): void
    {
        $response = new Response(400, ['Content-Type' => 'application/json'], '{"success":false,"message":"BadRequest","error":true,"code":400}');

        $e = HttpClientException::badRequest($response);

        $this->assertSame(400, $e->getCode());
        $this->assertSame(400, $e->getResponseCode());
        $this->assertStringContainsString('BadRequest', $e->getMessage());
        $this->assertSame($response, $e->getResponse());
    }

    public function testUnauthorized(): void
    {
        $e = HttpClientException::unauthorized(new Response(401));

        $this->assertSame(401, $e->getCode());
        $this->assertSame('Your credentials are incorrect.', $e->getMessage());
    }

    public function testNotFoundFallsBackToDefaultMessage(): void
    {
        $e = HttpClientException::notFound(new Response(404));

        $this->assertSame(404, $e->getCode());
        $this->assertNotEmpty($e->getMessage());
    }

    public function testNotFoundPrefersServerMessage(): void
    {
        $response = new Response(404, ['Content-Type' => 'application/json'], '{"message":"No such address."}');

        $e = HttpClientException::notFound($response);

        $this->assertSame('No such address.', $e->getMessage());
    }

    public function testTooManyRequests(): void
    {
        $e = HttpClientException::tooManyRequests(new Response(429));

        $this->assertSame(429, $e->getCode());
    }

    public function testJsonResponseBodyIsDecoded(): void
    {
        $response = new Response(403, ['Content-Type' => 'application/json'], '{"success":false,"message":"Forbidden"}');

        $e = HttpClientException::forbidden($response);

        $this->assertSame(['success' => false, 'message' => 'Forbidden'], $e->getResponseBody());
    }

    public function testNonJsonResponseBodyIsKeptRaw(): void
    {
        $response = new Response(400, ['Content-Type' => 'text/html'], '<html>proxy error</html>');

        $e = HttpClientException::badRequest($response);

        $this->assertSame(['message' => '<html>proxy error</html>'], $e->getResponseBody());
    }
}
