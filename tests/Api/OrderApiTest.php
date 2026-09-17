<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\Api;

use SarissaOps\Enviatodo\Api\OrderApi;
use SarissaOps\Enviatodo\Exception\EnviatodoException;
use SarissaOps\Enviatodo\Exception\InvalidArgumentException;
use SarissaOps\Enviatodo\Model\Order\Order;
use SarissaOps\Enviatodo\Model\Order\OrderCreated;
use SarissaOps\Enviatodo\Model\Order\TransactionUser;

final class OrderApiTest extends TestCase
{
    protected function apiClass(): string
    {
        return OrderApi::class;
    }

    public function testCreatePostsOrderEnvelope(): void
    {
        $api = $this->apiWithFixture('order_created.json');
        assert($api instanceof OrderApi);

        $created = $api->create('e1c3896a-2e71-485f-a729-7c21ff96fea7', '9', '11', false);

        $this->assertSame('POST', $this->builder->method);
        $this->assertSame('Api/create_order', $this->builder->uri);
        $this->assertSame(
            ['order' => ['type' => 'create_order', 'data' => [
                'uuid' => 'e1c3896a-2e71-485f-a729-7c21ff96fea7',
                'detail' => ['provider_id' => '9', 'provider_service_id' => '11', 'insurance' => false],
            ]]],
            $this->decodedBody(),
        );
        assert($created instanceof OrderCreated);
        $this->assertSame(150.21, $created->getSummary()->getTotal());
        $guides = $created->getGuides();
        $this->assertCount(1, $guides);
        $this->assertSame('300000000011270006IT89', $guides[0]->getTrackingId());
    }

    public function testCreateRejectsEmptyUuid(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $api = $this->apiWithFixture('order_created.json');
        assert($api instanceof OrderApi);
        $api->create('', '9', '11', false);
    }

    public function testCancelPostsFullBody(): void
    {
        $api = $this->apiWithFixture('order_cancelled.json');
        assert($api instanceof OrderApi);

        $api->cancel(['300000000011270006IT89'], '2025-02-27T17:23:42', '2025-03-04T17:23:42', '7', 'Guía de prueba', 'rates');

        $this->assertSame('POST', $this->builder->method);
        $this->assertSame('Api/cancel_order', $this->builder->uri);
        $body = $this->decodedBody();
        $this->assertSame(['300000000011270006IT89'], $body['tracking_ids']);
        $date = $body['date'] ?? null;
        $this->assertIsArray($date);
        $this->assertSame('2025-02-27T17:23:42', $date['cancelled_at']);
        $reason = $body['reason'] ?? null;
        $this->assertIsArray($reason);
        $this->assertSame('7', $reason['value']);
        $this->assertSame('rates', $body['order_type']);
    }

    public function testCancelNonexistentThrows(): void
    {
        $this->expectException(EnviatodoException::class);

        // The API answers HTTP 200 with success:false, error:false and null data here.
        $api = $this->apiWithFixture('error_cancel_bogus.json');
        assert($api instanceof OrderApi);
        $api->cancel(['NOEXISTE123'], '2025-01-01T00:00:00', '2025-01-08T00:00:00', '7', 't', 'rates');
    }

    public function testCreateBogusUuidThrows(): void
    {
        $this->expectException(EnviatodoException::class);

        // The API answers HTTP 200 with error:true and null data here; the message names the missing transaction.
        $api = $this->apiWithFixture('error_create_bogus_uuid.json');
        assert($api instanceof OrderApi);
        $api->create('00000000-0000-0000-0000-000000000000', '9', '11', false);
    }

    public function testFilterEmptyCriteriaThrows(): void
    {
        $this->expectException(EnviatodoException::class);

        // The API answers the code as a string here.
        $api = $this->apiWithFixture('error_filter_empty.json');
        assert($api instanceof OrderApi);
        $api->filter([]);
    }

    public function testAllReturnsEmptyList(): void
    {
        $api = $this->apiWithFixture('orders.json');
        assert($api instanceof OrderApi);

        $this->assertSame([], $api->all());
    }

    public function testShowRequestsTransactionPath(): void
    {
        $api = $this->apiWithFixture('order_row.json');
        assert($api instanceof OrderApi);

        $rows = $api->show('162199');

        $this->assertSame('GET', $this->builder->method);
        $this->assertSame('Api/get_order/162199', $this->builder->uri);
        $this->assertIsArray($rows);
        $this->assertInstanceOf(Order::class, $rows[0]);
        $this->assertSame('162199', $rows[0]->getTrxId());
        $detail = $rows[0]->getDetail();
        $address = $detail['address'] ?? null;
        $this->assertIsArray($address);
        $origin = $address['origin'] ?? null;
        $this->assertIsArray($origin);
        $this->assertSame('64000', $origin['zip_code']);
        $guide = $detail['guide'] ?? null;
        $this->assertIsArray($guide);
        $this->assertSame('300000000011270006IT89', $guide['tracking_id']);
    }

    public function testFilterAlwaysSendsRecipient(): void
    {
        $api = $this->apiWithFixture('order_row.json');
        assert($api instanceof OrderApi);

        $api->filter(['date_range' => ['start' => '2024-03-28', 'end' => '2024-03-30'], 'provider' => 'all', 'status' => 'all', 'user_id' => 'all']);

        $this->assertSame('POST', $this->builder->method);
        $this->assertSame('Api/get_orders_filter', $this->builder->uri);
        $body = $this->decodedBody();
        $this->assertArrayHasKey('recipient', $body);
    }

    public function testTransactions(): void
    {
        $api = $this->apiWithFixture('transaction_row.json');
        assert($api instanceof OrderApi);

        $rows = $api->transactions();

        $this->assertSame('GET', $this->builder->method);
        $this->assertSame('Api/user_transactions', $this->builder->uri);
        $this->assertIsArray($rows);
        $this->assertInstanceOf(TransactionUser::class, $rows[0]);
        $this->assertSame('1', $rows[0]->getUserId());
    }

    public function testThrowsOnEnvelopeError(): void
    {
        $this->expectException(EnviatodoException::class);

        $api = $this->apiWithResponse($this->errorEnvelope());
        assert($api instanceof OrderApi);
        $api->show('1');
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
