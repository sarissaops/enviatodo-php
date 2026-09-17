<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\Api;

use SarissaOps\Enviatodo\Api\AddressApi;
use SarissaOps\Enviatodo\Exception\EnviatodoException;
use SarissaOps\Enviatodo\Exception\InvalidArgumentException;
use SarissaOps\Enviatodo\Model\Address\Address;
use SarissaOps\Enviatodo\Model\Address\DeleteResponse;
use SarissaOps\Enviatodo\Model\Address\SavedAddress;

final class AddressApiTest extends TestCase
{
    protected function apiClass(): string
    {
        return AddressApi::class;
    }

    public function testSavePostsFullAddressBody(): void
    {
        $api = $this->apiWithFixture('address_saved.json');
        assert($api instanceof AddressApi);

        $address = $api->save(self::payload());

        $this->assertSame('POST', $this->builder->method);
        $this->assertSame('Api/add_address', $this->builder->uri);
        $this->assertSame(self::payload(), $this->decodedBody());
        assert($address instanceof SavedAddress);
        $this->assertSame('17614', $address->getAddress()->getId());
        $this->assertSame('Cesar Alexis Fajardo Flores', $address->getAddress()->getFullName());
    }

    public function testSaveIncludesIdOnUpdate(): void
    {
        $api = $this->apiWithFixture('address_saved.json');
        assert($api instanceof AddressApi);

        $api->save(self::payload() + ['id' => '17614']);

        $body = $this->decodedBody();
        $this->assertSame('17614', $body['id']);
    }

    public function testAllReturnsEmptyList(): void
    {
        $api = $this->apiWithFixture('addresses.json');
        assert($api instanceof AddressApi);

        $this->assertSame([], $api->all());
    }

    public function testShowRequestsById(): void
    {
        $api = $this->apiWithFixture('address_row.json');
        assert($api instanceof AddressApi);

        $rows = $api->show(17638);

        $this->assertSame('GET', $this->builder->method);
        $this->assertSame('Api/get_address_by_id/17638', $this->builder->uri);
        $this->assertIsArray($rows);
        $this->assertInstanceOf(Address::class, $rows[0]);
        $this->assertSame('66612', $rows[0]->getZipCode());
        $this->assertSame('NL', $rows[0]->getStateCode());
    }

    public function testByTypeSendsClientHeader(): void
    {
        $api = $this->apiWithFixture('address_row.json');
        assert($api instanceof AddressApi);

        $api->byType(1);

        $this->assertSame('GET', $this->builder->method);
        $this->assertSame('Api/get_address_by_type_id/1', $this->builder->uri);
        $this->assertSame('1', $this->builder->headers['x-enviatodo-client']);
    }

    public function testDeleteUsesGet(): void
    {
        $api = $this->apiWithFixture('address_deleted.json');
        assert($api instanceof AddressApi);

        $deleted = $api->delete(1761555);

        $this->assertSame('GET', $this->builder->method);
        $this->assertSame('Api/delete_address_by_id/1761555', $this->builder->uri);
        assert($deleted instanceof DeleteResponse);
        $this->assertTrue($deleted->isDeleted());
    }

    public function testThrowsOnEnvelopeError(): void
    {
        $this->expectException(EnviatodoException::class);

        $api = $this->apiWithResponse($this->errorEnvelope());
        assert($api instanceof AddressApi);
        $api->show(1);
    }

    public function testSaveRejectsMissingRequiredKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $api = $this->apiWithFixture('address_saved.json');
        assert($api instanceof AddressApi);
        $api->save(['full_name' => 'No Address']);
    }

    public function testSaveAcceptsOptionalKeysMissing(): void
    {
        $api = $this->apiWithFixture('address_saved.json');
        assert($api instanceof AddressApi);

        $minimal = self::payload();
        unset($minimal['lat'], $minimal['lng'], $minimal['int_number'], $minimal['reference'], $minimal['default_addr']);

        $saved = $api->save($minimal);

        assert($saved instanceof SavedAddress);
        $this->assertSame('17614', $saved->getAddress()->getId());
    }

    public function testDeleteNonexistentThrows(): void
    {
        $this->expectException(EnviatodoException::class);

        // The API answers HTTP 200 with success:false, error:false and null data here.
        $api = $this->apiWithFixture('error_delete_missing.json');
        assert($api instanceof AddressApi);
        $api->delete(999999);
    }

    /**
     * @return array<string, mixed>
     */
    public static function payload(): array
    {
        return [
            'lat' => '0',
            'lng' => '0',
            'address_type_id' => '1',
            'full_name' => 'Cesar Alexis Fajardo Flores',
            'email' => 'cesaralexisff07@gmail.com',
            'telephone' => '4921952109',
            'street' => 'Fransico Zarco',
            'ext_number' => '940',
            'int_number' => '',
            'zip_code' => '64000',
            'suburb' => 'Monterrey Centro',
            'municipality' => 'Monterrey',
            'town' => 'Monterrey',
            'state' => 'Nuevo León',
            'state_code' => 'NL',
            'country_code' => 'MX',
            'reference' => 'Casa blanca12',
            'default_addr' => 'false',
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
