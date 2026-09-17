<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\Integration;

use PHPUnit\Framework\TestCase;
use SarissaOps\Enviatodo\Enviatodo;
use SarissaOps\Enviatodo\Model\Balance\Balance;
use SarissaOps\Enviatodo\Model\Catalog\Catalog;
use SarissaOps\Enviatodo\Model\Parcel\Carrier;
use SarissaOps\Enviatodo\Model\Parcel\ParcelService;
use SarissaOps\Enviatodo\Model\ZipCode\ZipCode;

/**
 * @group integration-readonly
 *
 * Live sandbox validation for every read-only endpoint with no dedicated
 * integration test yet. All calls are GETs (or read-only list fetches) —
 * nothing here can mutate the account. Skipped without ENVIATODO_TOKEN.
 */
final class ReadOnlyEndpointsIntegrationTest extends TestCase
{
    private Enviatodo $client;

    protected function setUp(): void
    {
        $token = getenv('ENVIATODO_TOKEN');
        if (!\is_string($token) || '' === $token) {
            $this->markTestSkipped('ENVIATODO_TOKEN is not set.');
        }

        $this->client = Enviatodo::create($token);
    }

    public function testBalanceAndZipCode(): void
    {
        $balance = $this->client->balance()->show();
        assert($balance instanceof Balance);
        $this->assertGreaterThanOrEqual(0.0, $balance->getBalance());

        $zip = $this->client->zipCode()->show('64764');
        assert($zip instanceof ZipCode);
        $this->assertSame('zip_code', $zip->getType());
        $this->assertNotEmpty($zip->getItems());
    }

    public function testCatalogs(): void
    {
        $pts = $this->client->catalog()->productTypes();
        assert($pts instanceof Catalog);
        $this->assertNotEmpty($pts->getItems());

        $pkt = $this->client->catalog()->packageTypes();
        assert($pkt instanceof Catalog);
        $this->assertNotEmpty($pkt->getItems());
    }

    public function testCarriersAndServices(): void
    {
        $carriers = $this->client->parcel()->carriers();
        $this->assertIsArray($carriers);
        $this->assertNotEmpty($carriers);
        $this->assertContainsOnlyInstancesOf(Carrier::class, $carriers);

        $services = $this->client->parcel()->services();
        $this->assertIsArray($services);
        $this->assertNotEmpty($services);
        $this->assertContainsOnlyInstancesOf(ParcelService::class, $services);
    }

    public function testEmptyListEndpoints(): void
    {
        $this->assertIsArray($this->client->address()->all());
        $this->assertIsArray($this->client->address()->byType(1));
        $this->assertIsArray($this->client->package()->all());
        $this->assertIsArray($this->client->order()->all());
        $this->assertIsArray($this->client->order()->transactions());
    }
}
