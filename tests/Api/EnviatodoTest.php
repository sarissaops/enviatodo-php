<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\Api;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use SarissaOps\Enviatodo\Api;
use SarissaOps\Enviatodo\Enviatodo;
use SarissaOps\Enviatodo\HttpClient\HttpClientConfigurator;

final class EnviatodoTest extends TestCase
{
    public function testFactoryMethodsExposeAllTenApiClasses(): void
    {
        $client = new Enviatodo((new HttpClientConfigurator())->setToken('t')->setHttpClient($this->mockClient()));

        $this->assertInstanceOf(Api\ZipCodeApi::class, $client->zipCode());
        $this->assertInstanceOf(Api\BalanceApi::class, $client->balance());
        $this->assertInstanceOf(Api\AddressApi::class, $client->address());
        $this->assertInstanceOf(Api\PackageApi::class, $client->package());
        $this->assertInstanceOf(Api\ParcelApi::class, $client->parcel());
        $this->assertInstanceOf(Api\QuoteApi::class, $client->quote());
        $this->assertInstanceOf(Api\OrderApi::class, $client->order());
        $this->assertInstanceOf(Api\GuideApi::class, $client->guide());
        $this->assertInstanceOf(Api\PickupApi::class, $client->pickup());
        $this->assertInstanceOf(Api\CatalogApi::class, $client->catalog());
    }

    public function testCreateAcceptsCustomEndpoint(): void
    {
        $configurator = (new HttpClientConfigurator())
            ->setToken('token')
            ->setEndpoint('https://api.enviatodo.mx/index.php/')
            ->setHttpClient($this->mockClient());

        $client = new Enviatodo($configurator);

        $this->assertInstanceOf(Api\BalanceApi::class, $client->balance());
    }

    private function mockClient(): \Psr\Http\Client\ClientInterface
    {
        $inner = $this->createMock(\Psr\Http\Client\ClientInterface::class);
        $inner->method('sendRequest')->willReturn(new \Nyholm\Psr7\Response(200));

        return $inner;
    }
}
