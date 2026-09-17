<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\Api;

use SarissaOps\Enviatodo\Api\BalanceApi;
use SarissaOps\Enviatodo\Exception\EnviatodoException;
use SarissaOps\Enviatodo\Exception\InvalidArgumentException;
use SarissaOps\Enviatodo\Model\Balance\Balance;

final class BalanceApiTest extends TestCase
{
    protected function apiClass(): string
    {
        return BalanceApi::class;
    }

    public function testShowRequestsBalanceEndpoint(): void
    {
        $api = $this->apiWithFixture('balance.json');

        assert($api instanceof BalanceApi);

        $balance = $api->show();

        $this->assertSame('GET', $this->builder->method);
        $this->assertSame('Api/get_client_balance', $this->builder->uri);
        $this->assertInstanceOf(Balance::class, $balance);
        $this->assertSame(10000.0, $balance->getBalance());
    }

    public function testShowThrowsOnEnvelopeError(): void
    {
        $this->expectException(EnviatodoException::class);

        $api = $this->apiWithResponse($this->errorEnvelope());
        assert($api instanceof BalanceApi);
        $api->show();
    }

    public function testShowThrowsOnHttpError(): void
    {
        $this->expectException(EnviatodoException::class);

        $api = $this->apiWithResponse($this->errorEnvelope('Unauthorized'));
        assert($api instanceof BalanceApi);
        $api->show();
    }
}
