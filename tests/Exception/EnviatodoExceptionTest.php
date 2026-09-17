<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\Exception;

use PHPUnit\Framework\TestCase;
use SarissaOps\Enviatodo\Exception as SdkException;
use SarissaOps\Enviatodo\Exception\EnviatodoException;

final class EnviatodoExceptionTest extends TestCase
{
    public function testBaseExceptionImplementsMarkerAndCarriesMessage(): void
    {
        $e = new EnviatodoException('envelope reported error:true', 200);

        $this->assertInstanceOf(SdkException::class, $e);
        $this->assertSame('envelope reported error:true', $e->getMessage());
        $this->assertSame(200, $e->getCode());
    }
}
