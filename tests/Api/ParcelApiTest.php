<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\Api;

use SarissaOps\Enviatodo\Api\ParcelApi;
use SarissaOps\Enviatodo\Exception\EnviatodoException;
use SarissaOps\Enviatodo\Model\Parcel\Carrier;
use SarissaOps\Enviatodo\Model\Parcel\ParcelService;
use SarissaOps\Enviatodo\Model\Parcel\Service;

final class ParcelApiTest extends TestCase
{
    protected function apiClass(): string
    {
        return ParcelApi::class;
    }

    public function testCarriersRequestsEndpoint(): void
    {
        $api = $this->apiWithFixture('carriers.json');
        assert($api instanceof ParcelApi);

        $carriers = $api->carriers();

        $this->assertSame('GET', $this->builder->method);
        $this->assertSame('Api/get_parcel_service', $this->builder->uri);
        $this->assertIsArray($carriers);
        $this->assertContainsOnlyInstancesOf(Carrier::class, $carriers);
        $this->assertCount(3, $carriers);
        $this->assertInstanceOf(Carrier::class, $carriers[0]);
        $this->assertSame('FDX', $carriers[0]->getTradeName());
        $this->assertSame('1', $carriers[0]->getProviderId());
        $this->assertNull($carriers[0]->getProviderConfig());
    }

    public function testServicesRequestsEndpoint(): void
    {
        $api = $this->apiWithFixture('services.json');
        assert($api instanceof ParcelApi);

        $services = $api->services();

        $this->assertSame('GET', $this->builder->method);
        $this->assertSame('Api/provider_services', $this->builder->uri);
        $this->assertIsArray($services);
        $this->assertContainsOnlyInstancesOf(ParcelService::class, $services);
        $this->assertCount(3, $services);
        $this->assertInstanceOf(ParcelService::class, $services[0]);
        $row = $services[0];
        $this->assertSame('FDX', $row->getParcel());
        $this->assertSame('1', $row->getProviderId());
        $nested = $row->getServices();
        $this->assertContainsOnlyInstancesOf(Service::class, $nested);
        $this->assertInstanceOf(Service::class, $nested[0]);
        $this->assertSame('1', $nested[0]->getProviderServiceId());
        $this->assertSame('FEDEX - Terrestre', $nested[0]->getLabel());
        $this->assertSame('TERRESTRE', $nested[0]->getViaTransport());
        $this->assertSame(68, $nested[0]->getMaxWeight());
    }

    public function testThrowsOnEnvelopeError(): void
    {
        $this->expectException(EnviatodoException::class);

        $api = $this->apiWithResponse($this->errorEnvelope());
        assert($api instanceof ParcelApi);
        $api->carriers();
    }
}
