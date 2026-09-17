<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\Integration;

use PHPUnit\Framework\TestCase;
use SarissaOps\Enviatodo\Enviatodo;
use SarissaOps\Enviatodo\Model\Quote\Quote;
use SarissaOps\Enviatodo\Tests\Api\QuoteApiTest;

/**
 * @group integration-readonly
 *
 * Live sandbox validation of all three quote modes through the real stack
 * (configurator headers, JSON body, envelope hydration). Skipped without
 * ENVIATODO_TOKEN. Read-only: quotes never mutate the account.
 */
final class QuoteIntegrationTest extends TestCase
{
    public function testAllThreeModesReturnRates(): void
    {
        $token = getenv('ENVIATODO_TOKEN');
        if (!\is_string($token) || '' === $token) {
            $this->markTestSkipped('ENVIATODO_TOKEN is not set.');
        }

        $client = Enviatodo::create($token);
        $quote = QuoteApiTest::quote();

        $byService = $client->quote()->byService($quote, 11);
        $byProvider = $client->quote()->byProvider($quote, 9);
        $all = $client->quote()->all($quote);

        foreach (['byService' => $byService, 'byProvider' => $byProvider, 'all' => $all] as $mode => $result) {
            assert($result instanceof Quote);

            $this->assertNotEmpty($result->getTransactionUuid(), "mode {$mode}: missing uuid");
            $this->assertNotEmpty($result->getRates(), "mode {$mode}: empty rates");
        }

        $last = $client->getLastResponse();
        $this->assertNotNull($last);
        $this->assertSame(200, $last->getStatusCode());
    }
}
