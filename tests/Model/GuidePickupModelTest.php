<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\Model;

use PHPUnit\Framework\TestCase;
use SarissaOps\Enviatodo\Model\Guide\GuideBinaries;
use SarissaOps\Enviatodo\Model\Guide\GuideFile;
use SarissaOps\Enviatodo\Model\Pickup\PickupOrder;

final class GuidePickupModelTest extends TestCase
{
    public function testGuideFileDefaults(): void
    {
        $this->assertSame('', GuideFile::create([])->getFileId());
    }

    public function testGuideBinariesDefaults(): void
    {
        $binaries = GuideBinaries::create([]);

        $this->assertSame([], $binaries->getFiles());
        $this->assertSame([], $binaries->getInvalidFiles());
    }

    public function testInvalidFileStringBinary(): void
    {
        $binaries = GuideBinaries::create(['invalid_files' => [['guide_id' => 7, 'binary' => 'false']]]);

        $this->assertSame('false', $binaries->getInvalidFiles()[0]->getBinary());
    }

    public function testPickupOrderDefaults(): void
    {
        $order = PickupOrder::create([]);

        $this->assertSame('', $order->getId());
        $this->assertSame('', $order->getTrackingId());
        $this->assertSame('', $order->getOrigin());
    }
}
