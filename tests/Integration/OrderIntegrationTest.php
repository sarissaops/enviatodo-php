<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\Integration;

use PHPUnit\Framework\TestCase;
use SarissaOps\Enviatodo\Enviatodo;
use SarissaOps\Enviatodo\Model\Order\Order;

/**
 * @group integration-readonly
 *
 * Live sandbox validation of the read-only order endpoints. create()/cancel()
 * are NEVER called here — create_order mints a real label against the balance.
 */
final class OrderIntegrationTest extends TestCase
{
    public function testReadOnlyEndpointsSucceed(): void
    {
        $token = getenv('ENVIATODO_TOKEN');
        if (!\is_string($token) || '' === $token) {
            $this->markTestSkipped('ENVIATODO_TOKEN is not set.');
        }

        $client = Enviatodo::create($token);

        $this->assertIsArray($client->order()->all());
        $this->assertIsArray($client->order()->show('162199'));

        $this->assertIsArray($client->order()->filter([
            'date_range' => ['start' => '2024-03-28', 'end' => '2024-03-30'],
            'provider' => 'all',
            'status' => 'all',
            'user_id' => 'all',
        ]));

        $this->assertIsArray($client->order()->transactions());
    }
}
