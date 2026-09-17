<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\Api;

use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SarissaOps\Enviatodo\Api\HttpApi;
use SarissaOps\Enviatodo\HttpClient\RequestBuilder;
use SarissaOps\Enviatodo\Hydrator\ModelHydrator;

/**
 * Shared harness for Api tests: a mock PSR-18 client serving a canned
 * response plus a recording RequestBuilder asserting method/URI/headers/body.
 */
abstract class TestCase extends PhpUnitTestCase
{
    protected RecordingBuilder $builder;

    /** @return class-string<HttpApi> */
    abstract protected function apiClass(): string;

    protected function apiWithFixture(string $fixtureFile): HttpApi
    {
        $json = file_get_contents(__DIR__ . '/../TestAssets/' . $fixtureFile);
        $this->assertNotFalse($json);

        return $this->apiWithResponse(new Response(200, ['Content-Type' => 'application/json'], $json));
    }

    protected function apiWithResponse(ResponseInterface $response): HttpApi
    {
        $inner = $this->createMock(ClientInterface::class);
        $inner->method('sendRequest')->willReturn($response);

        $this->builder = new RecordingBuilder();
        $class = $this->apiClass();

        return new $class($inner, $this->builder, new ModelHydrator());
    }

    protected function errorEnvelope(string $message = 'BadRequest'): ResponseInterface
    {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'success' => false,
            'message' => $message,
            'error' => true,
            'code' => 400,
        ], JSON_THROW_ON_ERROR));
    }
}

final class RecordingBuilder extends RequestBuilder
{
    public ?string $method = null;
    public ?string $uri = null;

    /** @var array<string, string> */
    public array $headers = [];

    public mixed $body = null;

    public function create(string $method, string $uri, array $headers = [], $body = null): RequestInterface
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->headers = $headers;
        $this->body = $body;

        return parent::create($method, $uri, $headers, $body);
    }
}
