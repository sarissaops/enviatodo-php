<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\Model;

use PHPUnit\Framework\TestCase;
use SarissaOps\Enviatodo\Model\Order\Order;
use SarissaOps\Enviatodo\Model\Order\OrderCreated;

final class OrderModelTest extends TestCase
{
    public function testDefaults(): void
    {
        $order = Order::create([]);

        $this->assertSame('', $order->getTrxId());
        $this->assertSame(
            ['address' => [], 'package' => [], 'recipient' => [], 'order' => [], 'guide' => [], 'fulfilment' => []],
            $order->getDetail(),
        );
    }

    public function testDetailSectionsNormalize(): void
    {
        $order = Order::create(['order_detail' => ['address' => ['origin' => []], 'guide' => null]]);

        $detail = $order->getDetail();
        $address = $detail['address'] ?? null;
        $this->assertIsArray($address);
        $this->assertSame([], $address['origin'] ?? null);
        $this->assertSame([], $detail['guide'] ?? null);
        $this->assertSame([], $detail['package'] ?? null);
    }

    public function testCreatedDefaults(): void
    {
        $created = OrderCreated::create([]);

        $this->assertSame(0.0, $created->getSummary()->getTotal());
        $this->assertSame([], $created->getGuides());
    }
}
