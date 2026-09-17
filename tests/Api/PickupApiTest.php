<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\Api;

use SarissaOps\Enviatodo\Api\PickupApi;
use SarissaOps\Enviatodo\Exception\EnviatodoException;
use SarissaOps\Enviatodo\Exception\InvalidArgumentException;
use SarissaOps\Enviatodo\Model\Pickup\PickupOrder;
use SarissaOps\Enviatodo\Model\Pickup\PickupResponse;

final class PickupApiTest extends TestCase
{
    protected function apiClass(): string
    {
        return PickupApi::class;
    }

    public function testOrdersForPickupPostsProvidersKey(): void
    {
        $api = $this->apiWithFixture('pickup_row.json');
        assert($api instanceof PickupApi);

        $rows = $api->ordersForPickup(['1']);

        $this->assertSame('POST', $this->builder->method);
        $this->assertSame('Api/get_orders_for_pickup_by_client_id', $this->builder->uri);
        $this->assertSame(['providers' => ['1']], $this->decodedBody());
        $this->assertIsArray($rows);
        $this->assertInstanceOf(PickupOrder::class, $rows[0]);
        $this->assertSame('162199', $rows[0]->getId());
        $this->assertSame('784499307983', $rows[0]->getTrackingId());
        $this->assertSame('FEDEX', $rows[0]->getProviderName());
    }

    public function testOrdersForPickupEmptyList(): void
    {
        $api = $this->apiWithFixture('pickup_eligible.json');
        assert($api instanceof PickupApi);

        $this->assertSame([], $api->ordersForPickup(['1']));
    }

    public function testCreatePostsFullBody(): void
    {
        $api = $this->apiWithFixture('pickup_ack.json');
        assert($api instanceof PickupApi);

        $created = $api->create(self::payload());

        $this->assertSame('POST', $this->builder->method);
        $this->assertSame('Api/add_pickup', $this->builder->uri);
        $this->assertSame(self::payload(), $this->decodedBody());
        assert($created instanceof PickupResponse);
        $this->assertTrue($created->isCreated());
    }

    public function testCreateRejectsMixedOrigins(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $payload = self::payload();
        $rows = $payload['data'] ?? null;
        $this->assertIsArray($rows);
        $rows[] = ['trx_id' => '426856', 'origin_address' => 'Otra dirección'];
        $payload['data'] = $rows;
        unset($payload['provider_id']);

        $api = $this->apiWithFixture('pickup_ack.json');
        assert($api instanceof PickupApi);
        $api->create($payload);
    }

    public function testCreateRejectsMissingKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $api = $this->apiWithFixture('pickup_ack.json');
        assert($api instanceof PickupApi);
        $api->create(['who_delivers' => 'Nobody']);
    }

    public function testCancelPostsFullBody(): void
    {
        $api = $this->apiWithFixture('pickup_ack.json');
        assert($api instanceof PickupApi);

        $cancelled = $api->cancel(29114, 'No utilizaré la guía', '1');

        $this->assertSame('POST', $this->builder->method);
        $this->assertSame('Api/cancel_pickup', $this->builder->uri);
        $this->assertSame(
            ['id_recollection' => 29114, 'cancel_description' => 'No utilizaré la guía', 'user_id' => '1'],
            $this->decodedBody(),
        );
        assert($cancelled instanceof PickupResponse);
        $this->assertTrue($cancelled->isCreated());
    }

    public function testThrowsOnEnvelopeError(): void
    {
        $this->expectException(EnviatodoException::class);

        $api = $this->apiWithResponse($this->errorEnvelope());
        assert($api instanceof PickupApi);
        $api->ordersForPickup(['1']);
    }

    /**
     * @return array<string, mixed>
     */
    public static function payload(): array
    {
        return [
            'who_delivers' => 'Oswaldo Vazquez',
            'contact_phone' => '8888888888',
            'contact_email' => 'oswaldo@example.com',
            'data' => [[
                'trx_id' => '426855',
                'origin_address' => 'Fransico Zarco, 940, Monterrey Centro, Monterrey, Nuevo León, 64000, MX',
            ]],
            'pickup_date' => '2023-07-30 06:13 pm',
            'pickup_place' => 2,
            'pickup_place_data' => 'OFICINA',
            'origin' => 'Fransico Zarco, 940, Monterrey Centro, Monterrey, Nuevo León, 64000, MX',
            'provider_id' => '1',
            'description_reference' => 'prueba',
        ];
    }

    /**
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
