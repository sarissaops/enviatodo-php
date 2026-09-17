<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\Api;

use SarissaOps\Enviatodo\Api\CatalogApi;
use SarissaOps\Enviatodo\Exception\EnviatodoException;
use SarissaOps\Enviatodo\Model\Catalog\Catalog;

final class CatalogApiTest extends TestCase
{
    protected function apiClass(): string
    {
        return CatalogApi::class;
    }

    public function testProductTypesRequestsPtsEndpoint(): void
    {
        $api = $this->apiWithFixture('catalog_pts.json');
        assert($api instanceof CatalogApi);

        $catalog = $api->productTypes();

        $this->assertSame('GET', $this->builder->method);
        $this->assertSame('Api/get_catalog/pts', $this->builder->uri);
        assert($catalog instanceof Catalog);
        $this->assertSame('catalog', $catalog->getComponent());
        $this->assertCount(1822, $catalog->getItems());
        $this->assertSame('01010101', $catalog->getItems()[0]['key']);
    }

    public function testPackageTypesRequestsPktEndpoint(): void
    {
        $api = $this->apiWithFixture('catalog_pkt.json');
        assert($api instanceof CatalogApi);

        $catalog = $api->packageTypes();

        $this->assertSame('GET', $this->builder->method);
        $this->assertSame('Api/get_catalog/pkt', $this->builder->uri);
        assert($catalog instanceof Catalog);
        $this->assertCount(471, $catalog->getItems());
    }

    public function testBadCatalogCodeLiveShape(): void
    {
        $api = $this->apiWithFixture('error_bad_catalog.json');
        assert($api instanceof CatalogApi);

        try {
            $api->productTypes();
            $this->fail('Expected EnviatodoException.');
        } catch (EnviatodoException $e) {
            $this->assertSame(400, $e->getCode());
            $this->assertSame('Invalid param', $e->getMessage());
        }
    }

    public function testThrowsOnEnvelopeError(): void
    {
        $this->expectException(EnviatodoException::class);

        $api = $this->apiWithResponse($this->errorEnvelope());
        assert($api instanceof CatalogApi);
        $api->productTypes();
    }
}
