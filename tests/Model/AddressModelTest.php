<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\Model;

use PHPUnit\Framework\TestCase;
use SarissaOps\Enviatodo\Model\Address\Address;

final class AddressModelTest extends TestCase
{
    public function testDefaults(): void
    {
        $address = Address::create([]);

        $this->assertSame('', $address->getId());
        $this->assertSame('', $address->getFullName());
        $this->assertSame('', $address->getIntNumber());
        $this->assertSame('', $address->getCompany());
        $this->assertSame('', $address->getLat());
    }

    public function testLooseTypingIsNormalized(): void
    {
        $address = Address::create([
            'id' => 17614,
            'address_type_id' => 1,
            'lat' => 25.79,
            'lng' => -100.26,
            'default_addr' => '0',
            'company' => null,
        ]);

        $this->assertSame('17614', $address->getId());
        $this->assertSame('1', $address->getAddressTypeId());
        $this->assertSame('25.79', $address->getLat());
        $this->assertSame('-100.26', $address->getLng());
        $this->assertSame('0', $address->getDefaultAddr());
        $this->assertSame('', $address->getCompany());
    }
}
