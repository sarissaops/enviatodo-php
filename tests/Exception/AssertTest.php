<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\Exception;

use PHPUnit\Framework\TestCase;
use SarissaOps\Enviatodo\Assert;
use SarissaOps\Enviatodo\Exception\InvalidArgumentException;

final class AssertTest extends TestCase
{
    public function testFailedAssertionThrowsOwnException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Assert::stringNotEmpty('');
    }

    public function testPassingAssertionDoesNotThrow(): void
    {
        Assert::stringNotEmpty('64000');

        $this->addToAssertionCount(1);
    }

    public function testOriginalViolationMessageIsPreserved(): void
    {
        try {
            Assert::range(0, 1, 1000, 'Limit must be between 1 and 1000');
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('Limit must be between 1 and 1000', $e->getMessage());
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
        }
    }
}
