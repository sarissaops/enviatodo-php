<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\HttpClient;

use PHPUnit\Framework\TestCase;
use SarissaOps\Enviatodo\HttpClient\RequestBuilder;

final class RequestBuilderTest extends TestCase
{
    public function testArrayBodyIsJsonEncoded(): void
    {
        $request = (new RequestBuilder())->create('POST', 'Api/rates_client', [], ['type' => 'order']);

        $this->assertSame('application/json', $request->getHeaderLine('Content-Type'));
        $this->assertSame('{"type":"order"}', $request->getBody()->__toString());
        $this->assertSame('POST', $request->getMethod());
    }

    public function testStringBodyPassesThrough(): void
    {
        $request = (new RequestBuilder())->create('POST', 'Api/x', [], 'raw');

        $this->assertSame('raw', $request->getBody()->__toString());
    }

    public function testGetRequestHasEmptyBody(): void
    {
        $request = (new RequestBuilder())->create('GET', 'Api/get_client_balance');

        $this->assertSame('', $request->getBody()->__toString());
        $this->assertSame('Api/get_client_balance', (string) $request->getUri());
    }
}
