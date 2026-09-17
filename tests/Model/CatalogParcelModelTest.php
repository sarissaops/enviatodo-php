<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\Model;

use PHPUnit\Framework\TestCase;
use SarissaOps\Enviatodo\Model\Catalog\Catalog;
use SarissaOps\Enviatodo\Model\Parcel\Carrier;
use SarissaOps\Enviatodo\Model\Parcel\ParcelService;
use SarissaOps\Enviatodo\Model\Parcel\Service;

final class CatalogParcelModelTest extends TestCase
{
    public function testCatalogDefaults(): void
    {
        $catalog = Catalog::create([]);

        $this->assertSame('', $catalog->getComponent());
        $this->assertSame('', $catalog->getType());
        $this->assertSame([], $catalog->getItems());
    }

    public function testCatalogItemsNormalizeMissingKeys(): void
    {
        $catalog = Catalog::create(['items' => [['key' => 'X'], 'junk', ['value' => 'Y']]]);

        $this->assertSame(
            [['key' => 'X', 'value' => ''], ['key' => '', 'value' => 'Y']],
            $catalog->getItems(),
        );
    }

    public function testCarrierDefaults(): void
    {
        $carrier = Carrier::create([]);

        $this->assertSame('', $carrier->getTradeName());
        $this->assertSame('', $carrier->getProviderId());
        $this->assertNull($carrier->getProviderConfig());
    }

    public function testParcelServiceDefaults(): void
    {
        $row = ParcelService::create([]);

        $this->assertSame('', $row->getParcel());
        $this->assertSame('', $row->getProviderId());
        $this->assertSame([], $row->getServices());
    }

    public function testServiceDefaultsAndCoercion(): void
    {
        $service = Service::create(['config' => ['max_weight' => '68.5']]);

        $this->assertSame('', $service->getProviderServiceId());
        $this->assertSame('', $service->getLabel());
        $this->assertSame(68, $service->getMaxWeight());
        $this->assertSame(0, $service->getMaxHeight());
    }
}
