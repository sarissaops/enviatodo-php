<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\Model;

use PHPUnit\Framework\TestCase;
use SarissaOps\Enviatodo\Model\Package\Package;

final class PackageModelTest extends TestCase
{
    public function testDefaults(): void
    {
        $package = Package::create([]);

        $this->assertSame('', $package->getId());
        $this->assertSame('', $package->getName());
        $this->assertSame('', $package->getDefaultPkg());
    }

    public function testDefaultKeyVariance(): void
    {
        $fromList = Package::create(['default' => '1']);
        $fromSingle = Package::create(['default_pkg' => '1']);

        $this->assertSame('1', $fromList->getDefaultPkg());
        $this->assertSame('1', $fromSingle->getDefaultPkg());
    }

    public function testLooseTypingIsNormalized(): void
    {
        $package = Package::create([
            'id' => 5984,
            'height' => 5,
            'weight' => 0.5,
            'amount_pkg' => '500.00',
        ]);

        $this->assertSame('5984', $package->getId());
        $this->assertSame('5', $package->getHeight());
        $this->assertSame('0.5', $package->getWeight());
        $this->assertSame('500.00', $package->getAmountPkg());
    }
}
