<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\Integration;

use PHPUnit\Framework\TestCase;
use SarissaOps\Enviatodo\Enviatodo;

/**
 * @group integration-readonly
 *
 * Live sandbox validation of the read-only pickup endpoint. add_pickup and
 * cancel_pickup are mutating and are NEVER called here.
 */
final class PickupIntegrationTest extends TestCase
{
    public function testOrdersForPickupSucceeds(): void
    {
        $token = getenv('ENVIATODO_TOKEN');
        if (!\is_string($token) || '' === $token) {
            $this->markTestSkipped('ENVIATODO_TOKEN is not set.');
        }

        $this->assertIsArray(Enviatodo::create($token)->pickup()->ordersForPickup(['1']));
    }
}
