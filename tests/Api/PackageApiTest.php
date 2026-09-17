<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\Api;

use SarissaOps\Enviatodo\Api\PackageApi;
use SarissaOps\Enviatodo\Exception\EnviatodoException;
use SarissaOps\Enviatodo\Exception\InvalidArgumentException;
use SarissaOps\Enviatodo\Model\Package\DeleteResponse;
use SarissaOps\Enviatodo\Model\Package\SavedPackage;

final class PackageApiTest extends TestCase
{
    protected function apiClass(): string
    {
        return PackageApi::class;
    }

    public function testSavePostsFullPackageBody(): void
    {
        $api = $this->apiWithFixture('package_saved.json');
        assert($api instanceof PackageApi);

        $saved = $api->save(self::payload());

        $this->assertSame('POST', $this->builder->method);
        $this->assertSame('Api/add_package/', $this->builder->uri);
        $this->assertSame(self::payload(), $this->decodedBody());
        assert($saved instanceof SavedPackage);
        $this->assertSame('5984', $saved->getPackage()->getId());
        $this->assertSame('CAJA CHICA', $saved->getPackage()->getName());
    }

    public function testSaveIncludesIdOnUpdate(): void
    {
        $api = $this->apiWithFixture('package_saved.json');
        assert($api instanceof PackageApi);

        $api->save(self::payload() + ['id' => '5984']);

        $body = $this->decodedBody();
        $this->assertSame('5984', $body['id']);
    }

    public function testAllReturnsEmptyList(): void
    {
        $api = $this->apiWithFixture('packages.json');
        assert($api instanceof PackageApi);

        $this->assertSame([], $api->all());
    }

    public function testShowRequestsById(): void
    {
        $api = $this->apiWithFixture('package_row.json');
        assert($api instanceof PackageApi);

        $rows = $api->show(6001);

        $this->assertSame('GET', $this->builder->method);
        $this->assertSame('Api/get_package_by_id/6001', $this->builder->uri);
        $this->assertIsArray($rows);
        $this->assertInstanceOf(\SarissaOps\Enviatodo\Model\Package\Package::class, $rows[0]);
        $this->assertSame('PAKETAZO654465', $rows[0]->getName());
        $this->assertSame('1.00', $rows[0]->getBillWeight());
    }

    public function testDeleteUsesGet(): void
    {
        $api = $this->apiWithFixture('package_deleted.json');
        assert($api instanceof PackageApi);

        $deleted = $api->delete(501);

        $this->assertSame('GET', $this->builder->method);
        $this->assertSame('Api/delete_package/501', $this->builder->uri);
        assert($deleted instanceof DeleteResponse);
        $this->assertTrue($deleted->isDeleted());
    }

    public function testThrowsOnEnvelopeError(): void
    {
        $this->expectException(EnviatodoException::class);

        $api = $this->apiWithResponse($this->errorEnvelope());
        assert($api instanceof PackageApi);
        $api->show(1);
    }

    public function testSaveRejectsMissingRequiredKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $api = $this->apiWithFixture('package_saved.json');
        assert($api instanceof PackageApi);
        $api->save(['name' => 'caja chica']);
    }

    public function testSaveAcceptsNameMissing(): void
    {
        $api = $this->apiWithFixture('package_saved.json');
        assert($api instanceof PackageApi);

        $payload = self::payload();
        unset($payload['name']);

        $saved = $api->save($payload);

        assert($saved instanceof SavedPackage);
        $this->assertSame('5984', $saved->getPackage()->getId());
    }

    public function testDeleteNonexistentThrows(): void
    {
        $this->expectException(EnviatodoException::class);

        // The API answers HTTP 200 with success:false, error:false and null data here.
        $api = $this->apiWithFixture('error_delete_missing.json');
        assert($api instanceof PackageApi);
        $api->delete(999999);
    }

    /**
     * @return array<string, mixed>
     */
    public static function payload(): array
    {
        return [
            'name' => 'caja chica',
            'product_type' => '47131900',
            'unit_type' => 'XBX',
            'package_content' => 'PLAYERAS',
            'amount_pkg' => '150',
            'height' => 13,
            'width' => 11,
            'length' => 10,
            'weight' => 1,
            'real_weight' => '1.00',
            'volumetric_weight' => '0.22',
            'bill_weight' => '1.00',
            'default_pkg' => 'false',
        ];
    }

    /**
     * The recording builder captures the pre-encoding PHP value.
     *
     * @return array<string, mixed>
     */
    private function decodedBody(): array
    {
        $body = $this->builder->body;
        if (\is_string($body)) {
            $decoded = json_decode($body, true);
            if (!\is_array($decoded)) {
                $this->fail('Expected the recorded request body to be a JSON object.');
            }
            $body = $decoded;
        }
        if (!\is_array($body)) {
            $this->fail('Expected the recorded request body to be an array.');
        }

        /** @var array<string, mixed> $clean */
        $clean = [];
        foreach ($body as $key => $value) {
            $clean[(string) $key] = $value;
        }

        return $clean;
    }
}
