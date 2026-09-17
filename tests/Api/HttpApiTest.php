<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\Api;

use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SarissaOps\Enviatodo\Api\HttpApi;
use SarissaOps\Enviatodo\Exception\HttpClientException;
use SarissaOps\Enviatodo\Exception\HttpServerException;
use SarissaOps\Enviatodo\Exception\UnknownErrorException;
use SarissaOps\Enviatodo\HttpClient\RequestBuilder;
use SarissaOps\Enviatodo\Hydrator\ModelHydrator;

final class HttpApiTest extends TestCase
{
    public function testHttpGetAppendsQueryString(): void
    {
        $api = $this->api(new Response(200, ['Content-Type' => 'application/json'], '{"success":true,"message":"ok","data":{},"error":false,"code":200}'));

        $api->exposedGet('Api/get_orders', ['limit' => '5']);

        $this->assertSame('GET', $api->builder->method);
        $this->assertSame('Api/get_orders?limit=5', $api->builder->uri);
    }

    public function testPerCallHeadersAreForwarded(): void
    {
        $api = $this->api(new Response(200, ['Content-Type' => 'application/json'], '{"success":true,"message":"ok","data":[],"error":false,"code":200}'));

        $api->exposedGet('Api/get_address_by_type_id/1', [], ['x-enviatodo-client' => '1']);

        $this->assertSame(['x-enviatodo-client' => '1'], $api->builder->headers);
    }

    public function testStatusErrorMap(): void
    {
        foreach ([400, 401, 404, 429] as $status) {
            try {
                $this->api(new Response($status))->exposedGet('Api/x');
                $this->fail("Expected HttpClientException for HTTP {$status}.");
            } catch (HttpClientException $e) {
                $this->assertSame($status, $e->getCode());
            }
        }

        try {
            $this->api(new Response(503))->exposedGet('Api/x');
            $this->fail('Expected HttpServerException for HTTP 503.');
        } catch (HttpServerException $e) {
            $this->assertSame(503, $e->getCode());
        }

        try {
            $this->api(new Response(302))->exposedGet('Api/x');
            $this->fail('Expected UnknownErrorException for HTTP 302.');
        } catch (UnknownErrorException) {
            $this->addToAssertionCount(1);
        }
    }

    public function testNetworkFailureBecomesHttpServerException(): void
    {
        $inner = $this->createMock(ClientInterface::class);
        $inner->method('sendRequest')->willThrowException(new StubNetworkException());

        $api = new StubApi($inner, new RecordingBuilder(), new ModelHydrator());

        try {
            $api->exposedGet('Api/x');
            $this->fail('Expected HttpServerException.');
        } catch (HttpServerException $e) {
            $this->assertInstanceOf(ClientExceptionInterface::class, $e->getPrevious());
        }
    }

    private function api(ResponseInterface $response): StubApi
    {
        $inner = $this->createMock(ClientInterface::class);
        $inner->method('sendRequest')->willReturn($response);

        return new StubApi($inner, new RecordingBuilder(), new ModelHydrator());
    }
}

final class StubApi extends HttpApi
{
    public RecordingBuilder $builder;

    public function __construct(ClientInterface $httpClient, RecordingBuilder $builder, \SarissaOps\Enviatodo\Hydrator\Hydrator $hydrator)
    {
        $this->builder = $builder;
        parent::__construct($httpClient, $builder, $hydrator);
    }

    /**
     * @param array<string, string> $parameters
     * @param array<string, string> $headers
     *
     * @return mixed
     */
    public function exposedGet(string $path, array $parameters = [], array $headers = [])
    {
        $response = $this->httpGet($path, $parameters, $headers);

        return $this->hydrateResponse($response, StubPing::class);
    }
}

final class StubPing implements \SarissaOps\Enviatodo\Model\ApiResponse
{
    /** @param array<array-key, mixed> $data */
    public static function create(array $data): static
    {
        return new self();
    }
}

final class StubNetworkException extends \RuntimeException implements \Psr\Http\Client\NetworkExceptionInterface
{
    public function getRequest(): RequestInterface
    {
        return new \Nyholm\Psr7\Request('GET', '/');
    }
}
