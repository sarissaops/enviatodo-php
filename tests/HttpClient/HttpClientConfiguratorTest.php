<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\HttpClient;

use Http\Client\Common\PluginClient;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use SarissaOps\Enviatodo\HttpClient\HttpClientConfigurator;

final class HttpClientConfiguratorTest extends TestCase
{
    public function testConfiguredClientSendsThreeAuthHeadersToEndpointHost(): void
    {
        $seen = null;
        $inner = $this->createMock(\Psr\Http\Client\ClientInterface::class);
        $inner->method('sendRequest')->willReturnCallback(function (RequestInterface $request) use (&$seen) {
            $seen = $request;

            return new Response(200);
        });

        $configurator = (new HttpClientConfigurator())
            ->setToken('test-token')
            ->setEndpoint('https://apiqav2.enviatodo.mx/index.php/')
            ->setHttpClient($inner);

        $client = $configurator->createConfiguredClient();

        $this->assertInstanceOf(PluginClient::class, $client);
        $client->sendRequest(new Request('GET', 'Api/get_client_balance'));

        $this->assertInstanceOf(RequestInterface::class, $seen);
        $this->assertSame('apiqav2.enviatodo.mx', $seen->getUri()->getHost());
        $this->assertSame('/index.php/Api/get_client_balance', $seen->getUri()->getPath());
        $this->assertSame('Bearer test-token', $seen->getHeaderLine('Authorization'));
        $this->assertSame('enviatodo', $seen->getHeaderLine('x-api-key'));
        $this->assertSame('custom', $seen->getHeaderLine('x-enviatodo-app'));
    }

    public function testCustomApiKeyAndAppNameAreHonored(): void
    {
        $seen = null;
        $inner = $this->createMock(\Psr\Http\Client\ClientInterface::class);
        $inner->method('sendRequest')->willReturnCallback(function (RequestInterface $request) use (&$seen) {
            $seen = $request;

            return new Response(200);
        });

        $configurator = (new HttpClientConfigurator())
            ->setToken('t')
            ->setApiKey('other-key')
            ->setAppName('other-app')
            ->setHttpClient($inner);

        $configurator->createConfiguredClient()->sendRequest(new Request('GET', 'Api/get_client_balance'));

        $this->assertInstanceOf(RequestInterface::class, $seen);
        $this->assertSame('other-key', $seen->getHeaderLine('x-api-key'));
        $this->assertSame('other-app', $seen->getHeaderLine('x-enviatodo-app'));
    }

    public function testPathlessEndpointLeavesRequestPathUntouched(): void
    {
        $seen = null;
        $inner = $this->createMock(\Psr\Http\Client\ClientInterface::class);
        $inner->method('sendRequest')->willReturnCallback(function (RequestInterface $request) use (&$seen) {
            $seen = $request;

            return new Response(200);
        });

        $configurator = (new HttpClientConfigurator())
            ->setToken('t')
            ->setEndpoint('https://api.enviatodo.mx/')
            ->setHttpClient($inner);

        $configurator->createConfiguredClient()->sendRequest(new Request('GET', 'Api/get_client_balance'));

        $this->assertInstanceOf(RequestInterface::class, $seen);
        $this->assertSame('api.enviatodo.mx', $seen->getUri()->getHost());
        $this->assertSame('/Api/get_client_balance', $seen->getUri()->getPath());
        $this->assertSame('Bearer t', $seen->getHeaderLine('Authorization'));
    }

    public function testTimeoutRoundTrip(): void
    {
        $configurator = new HttpClientConfigurator();

        $this->assertNull($configurator->getTimeout());
        $this->assertSame($configurator, $configurator->setTimeout(10.0));
        $this->assertSame(10.0, $configurator->getTimeout());
    }

    public function testTimeoutRejectsNonPositive(): void
    {
        $this->expectException(\SarissaOps\Enviatodo\Exception\InvalidArgumentException::class);

        (new HttpClientConfigurator())->setTimeout(0.0);
    }

    public function testMissingTokenFailsFast(): void
    {
        $this->expectException(\SarissaOps\Enviatodo\Exception\InvalidArgumentException::class);

        (new HttpClientConfigurator())->createConfiguredClient();
    }

    public function testLastResponseIsTracked(): void
    {
        $inner = $this->createMock(\Psr\Http\Client\ClientInterface::class);
        $inner->method('sendRequest')->willReturn(new Response(200));

        $configurator = (new HttpClientConfigurator())->setToken('t')->setHttpClient($inner);
        $configurator->createConfiguredClient()->sendRequest(new Request('GET', 'Api/get_client_balance'));

        $this->assertInstanceOf(Response::class, $configurator->getResponseHistory()->getLastResponse());
    }
}
