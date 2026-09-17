<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\Api;

use SarissaOps\Enviatodo\Api\QuoteApi;
use SarissaOps\Enviatodo\Exception\EnviatodoException;
use SarissaOps\Enviatodo\Exception\InvalidArgumentException;
use SarissaOps\Enviatodo\Model\Quote\Quote;

final class QuoteApiTest extends TestCase
{
    protected function apiClass(): string
    {
        return QuoteApi::class;
    }

    public function testByServicePostsServiceKeyOnly(): void
    {
        $api = $this->apiWithFixture('rates_service.json');
        assert($api instanceof QuoteApi);

        $quote = $api->byService(self::quote(), 11);

        $this->assertSame('POST', $this->builder->method);
        $this->assertSame('Api/rates_client', $this->builder->uri);
        $body = $this->decodedBody();
        $quotes = $body['quotes'] ?? null;
        $this->assertIsArray($quotes);
        $this->assertSame(11, $quotes['provider_service_id']);
        $this->assertArrayNotHasKey('provider_id', $quotes);
        assert($quote instanceof Quote);
        $this->assertCount(1, $quote->getRates());
        $this->assertNotEmpty($quote->getTransactionUuid());
    }

    public function testByProviderPostsProviderKeyOnly(): void
    {
        $api = $this->apiWithFixture('rates_provider.json');
        assert($api instanceof QuoteApi);

        $quote = $api->byProvider(self::quote(), 9);

        $body = $this->decodedBody();
        $quotes = $body['quotes'] ?? null;
        $this->assertIsArray($quotes);
        $this->assertSame(9, $quotes['provider_id']);
        $this->assertArrayNotHasKey('provider_service_id', $quotes);
        assert($quote instanceof Quote);
        $this->assertCount(2, $quote->getRates());
    }

    public function testAllPostsNeitherKey(): void
    {
        $api = $this->apiWithFixture('rates_all.json');
        assert($api instanceof QuoteApi);

        $quote = $api->all(self::quote());

        $body = $this->decodedBody();
        $quotes = $body['quotes'] ?? null;
        $this->assertIsArray($quotes);
        $this->assertArrayNotHasKey('provider_id', $quotes);
        $this->assertArrayNotHasKey('provider_service_id', $quotes);
        assert($quote instanceof Quote);
        $this->assertCount(4, $quote->getRates());
    }

    public function testRateRowGetters(): void
    {
        $api = $this->apiWithFixture('rates_all.json');
        assert($api instanceof QuoteApi);

        $quote = $api->all(self::quote());
        assert($quote instanceof Quote);
        $row = $quote->getRates()[0];

        $this->assertSame('9', $row->getProviderId());
        $this->assertSame('11', $row->getProviderServiceId());
        $this->assertSame('Dia Sig.', $row->getServiceName());
        $this->assertSame('AEREO', $row->getViaTransport());
        $this->assertSame('En domicilio', $row->getDeliveryMode());
        $this->assertNotEmpty($row->getEstimatedDate());
        $charges = $row->getCharges();
        $this->assertSame('base', $charges[0]->getType());
        $this->assertSame(129.49, $charges[0]->getSubTotal());
        $this->assertSame(20.72, $charges[0]->getTax());
        $this->assertSame(150.21, $charges[0]->getTotal());
        $this->assertSame([], $row->getOffer());
    }

    public function testRateRowCurrencyFromDetailCharges(): void
    {
        $api = $this->apiWithFixture('rates_all.json');
        assert($api instanceof QuoteApi);

        $quote = $api->all(self::quote());
        assert($quote instanceof Quote);
        $this->assertSame('MXN', $quote->getRates()[0]->getCurrency());
    }

    public function testRateRowCurrencyNullWhenAbsent(): void
    {
        $api = $this->apiWithResponse(new \Nyholm\Psr7\Response(200, ['Content-Type' => 'application/json'], '{"success":true,"message":"success","data":{"transaction":{},"rates":[{"provider_id":"1"}],"packages":{}},"error":false,"code":200}'));
        assert($api instanceof QuoteApi);

        $quote = $api->all(self::quote());
        assert($quote instanceof Quote);
        $this->assertNull($quote->getRates()[0]->getCurrency());
    }

    public function testMissingWeightKeysThrowPreHttp(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $quote = self::quote();
        $this->assertIsArray($quote['package']);
        unset($quote['package']['bill_weight']);

        $api = $this->apiWithFixture('rates_all.json');
        assert($api instanceof QuoteApi);
        $api->all($quote);
    }

    public function testThrowsOnEnvelopeError(): void
    {
        $this->expectException(EnviatodoException::class);

        $api = $this->apiWithResponse($this->errorEnvelope());
        assert($api instanceof QuoteApi);
        $api->all(self::quote());
    }

    public function testEmptyRates(): void
    {
        $api = $this->apiWithResponse(new \Nyholm\Psr7\Response(200, ['Content-Type' => 'application/json'], '{"success":true,"message":"success","data":{"transaction":{"type":"rates","timestamp":"2026-09-15 09:43:50","uuid":"u"},"rates":[],"packages":{"quantity":1}},"error":false,"code":200}'));
        assert($api instanceof QuoteApi);

        $quote = $api->all(self::quote());
        assert($quote instanceof Quote);
        $this->assertSame([], $quote->getRates());
        $this->assertSame('u', $quote->getTransactionUuid());
    }

    /**
     * @return array<string, mixed>
     */
    public static function quote(): array
    {
        return [
            'shipping_type' => '1',
            'quantity' => 1,
            'origin' => [
                'address_type_id' => '1',
                'full_name' => 'De la Vega',
                'email' => 'test@gmail.com',
                'telephone' => '5622712210',
                'street' => 'JUAN IGNACIO RAMON',
                'ext_number' => '321',
                'int_number' => '1A',
                'zip_code' => '64000',
                'suburb' => 'Monterrey Centro',
                'municipality' => 'Monterrey',
                'town' => 'Monterrey',
                'state' => 'Nuevo Leon',
                'state_code' => 'NL',
                'country_code' => 'MX',
            ],
            'destination' => [
                'address_type_id' => '2',
                'full_name' => 'ANTONIO',
                'email' => 'prueba@enviatodo.com',
                'telephone' => '5611025483',
                'street' => 'PASEO VIOLETAS',
                'ext_number' => '10',
                'int_number' => '',
                'zip_code' => '03300',
                'suburb' => 'Tabachines',
                'municipality' => 'Cuernavaca',
                'town' => 'Cuernavaca',
                'state' => 'Morelos',
                'state_code' => 'MS',
                'country_code' => 'MX',
            ],
            'package' => [
                'product_type' => '01010101',
                'unit_type' => 'X1A',
                'package_content' => 'qqq',
                'amount_pkg' => '20000',
                'height' => 10,
                'width' => 10,
                'length' => 10,
                'weight' => 1,
                'real_weight' => '1.00',
                'volumetric_weight' => '1',
                'bill_weight' => '1',
                'default_pkg' => '0',
            ],
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
