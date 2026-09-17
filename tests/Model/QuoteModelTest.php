<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\Model;

use PHPUnit\Framework\TestCase;
use SarissaOps\Enviatodo\Model\Quote\Charge;
use SarissaOps\Enviatodo\Model\Quote\Quote;
use SarissaOps\Enviatodo\Model\Quote\RateRow;

final class QuoteModelTest extends TestCase
{
    public function testDefaults(): void
    {
        $quote = Quote::create([]);

        $this->assertSame('', $quote->getTransactionUuid());
        $this->assertSame([], $quote->getRates());
        $this->assertSame(0, $quote->getPackageQuantity());
    }

    public function testRateRowDefaults(): void
    {
        $row = RateRow::create([]);

        $this->assertSame('', $row->getProviderId());
        $this->assertSame([], $row->getCharges());
        $this->assertSame([], $row->getDetailCharges());
        $this->assertSame([], $row->getOffer());
        $this->assertSame(0, $row->getPriority());
    }

    public function testPriorityIntAndNull(): void
    {
        $this->assertSame(0, RateRow::create(['priority' => null])->getPriority());
        $this->assertSame(3, RateRow::create(['priority' => 3])->getPriority());
    }

    public function testChargeDefaults(): void
    {
        $charge = Charge::create([]);

        $this->assertSame('', $charge->getType());
        $this->assertSame(0.0, $charge->getSubTotal());
        $this->assertSame(0.0, $charge->getTax());
        $this->assertSame(0.0, $charge->getTotal());
    }
}
