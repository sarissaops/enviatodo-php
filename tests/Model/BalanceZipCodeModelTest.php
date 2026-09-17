<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\Model;

use PHPUnit\Framework\TestCase;
use SarissaOps\Enviatodo\Model\Balance\Balance;
use SarissaOps\Enviatodo\Model\ZipCode\ZipCode;

final class BalanceZipCodeModelTest extends TestCase
{
    public function testBalanceCastsIntToFloat(): void
    {
        $this->assertSame(10000.0, Balance::create(['balance' => 10000])->getBalance());
    }

    public function testBalanceDefaultsToZero(): void
    {
        $this->assertSame(0.0, Balance::create([])->getBalance());
    }

    public function testBalanceCurrencyDefaultsToMxn(): void
    {
        $this->assertSame('MXN', Balance::create(['balance' => 1])->getCurrency());
    }

    public function testZipCodeDefaults(): void
    {
        $zip = ZipCode::create([]);

        $this->assertSame('', $zip->getComponent());
        $this->assertSame('', $zip->getType());
        $this->assertSame([], $zip->getItems());
    }
}
