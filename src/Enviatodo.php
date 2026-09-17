<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo;

use Http\Client\Common\PluginClient;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use SarissaOps\Enviatodo\Api\AddressApi;
use SarissaOps\Enviatodo\Api\BalanceApi;
use SarissaOps\Enviatodo\Api\CatalogApi;
use SarissaOps\Enviatodo\Api\GuideApi;
use SarissaOps\Enviatodo\Api\OrderApi;
use SarissaOps\Enviatodo\Api\PackageApi;
use SarissaOps\Enviatodo\Api\ParcelApi;
use SarissaOps\Enviatodo\Api\PickupApi;
use SarissaOps\Enviatodo\Api\QuoteApi;
use SarissaOps\Enviatodo\Api\ZipCodeApi;
use SarissaOps\Enviatodo\HttpClient\HttpClientConfigurator;
use SarissaOps\Enviatodo\HttpClient\Plugin\History;
use SarissaOps\Enviatodo\HttpClient\RequestBuilder;
use SarissaOps\Enviatodo\Hydrator\Hydrator;
use SarissaOps\Enviatodo\Hydrator\ModelHydrator;

/**
 * Entry point. Either use Enviatodo::create($token) or inject a preconfigured
 * HttpClientConfigurator (custom PSR-18 client, factories, debug mode).
 */
class Enviatodo
{
    /** @var ClientInterface|PluginClient */
    private $httpClient;

    private Hydrator $hydrator;

    private RequestBuilder $requestBuilder;

    private History $responseHistory;

    public function __construct(
        HttpClientConfigurator $configurator,
        ?Hydrator $hydrator = null,
        ?RequestBuilder $requestBuilder = null,
    ) {
        $this->requestBuilder = $requestBuilder ?: new RequestBuilder();
        $this->hydrator = $hydrator ?: new ModelHydrator();

        $this->httpClient = $configurator->createConfiguredClient();
        $this->responseHistory = $configurator->getResponseHistory();
    }

    public static function create(string $token, string $endpoint = HttpClientConfigurator::DEFAULT_ENDPOINT): self
    {
        $configurator = (new HttpClientConfigurator())
            ->setToken($token)
            ->setEndpoint($endpoint);

        return new self($configurator);
    }

    public function getLastResponse(): ?ResponseInterface
    {
        return $this->responseHistory->getLastResponse();
    }

    public function zipCode(): ZipCodeApi
    {
        return new ZipCodeApi($this->httpClient, $this->requestBuilder, $this->hydrator);
    }

    public function balance(): BalanceApi
    {
        return new BalanceApi($this->httpClient, $this->requestBuilder, $this->hydrator);
    }

    public function address(): AddressApi
    {
        return new AddressApi($this->httpClient, $this->requestBuilder, $this->hydrator);
    }

    public function package(): PackageApi
    {
        return new PackageApi($this->httpClient, $this->requestBuilder, $this->hydrator);
    }

    public function parcel(): ParcelApi
    {
        return new ParcelApi($this->httpClient, $this->requestBuilder, $this->hydrator);
    }

    public function quote(): QuoteApi
    {
        return new QuoteApi($this->httpClient, $this->requestBuilder, $this->hydrator);
    }

    public function order(): OrderApi
    {
        return new OrderApi($this->httpClient, $this->requestBuilder, $this->hydrator);
    }

    public function guide(): GuideApi
    {
        return new GuideApi($this->httpClient, $this->requestBuilder, $this->hydrator);
    }

    public function pickup(): PickupApi
    {
        return new PickupApi($this->httpClient, $this->requestBuilder, $this->hydrator);
    }

    public function catalog(): CatalogApi
    {
        return new CatalogApi($this->httpClient, $this->requestBuilder, $this->hydrator);
    }
}
