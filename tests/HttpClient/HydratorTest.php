<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\HttpClient;

use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use SarissaOps\Enviatodo\Exception\EnviatodoException;
use SarissaOps\Enviatodo\Exception\HydrationException;
use SarissaOps\Enviatodo\Hydrator\ArrayHydrator;
use SarissaOps\Enviatodo\Hydrator\ModelHydrator;
use SarissaOps\Enviatodo\Hydrator\NoopHydrator;
use SarissaOps\Enviatodo\Model\ApiResponse;

final class HydratorTest extends TestCase
{
    public function testModelHydratorUnwrapsEnvelopeData(): void
    {
        $response = new Response(200, ['Content-Type' => 'application/json'], <<<'JSON'
            {"success":true,"message":"success","data":{"balance":758.13},"error":false,"code":200}
            JSON);

        $model = (new ModelHydrator())->hydrate($response, StubBalance::class);

        $this->assertInstanceOf(StubBalance::class, $model);
        $this->assertSame(['balance' => 758.13], $model->received);
    }

    public function testModelHydratorThrowsOnEnvelopeErrorDespiteHttp200(): void
    {
        $response = new Response(200, ['Content-Type' => 'application/json'], <<<'JSON'
            {"success":false,"message":"BadRequest","error":true,"code":400}
            JSON);

        $this->expectException(EnviatodoException::class);

        (new ModelHydrator())->hydrate($response, StubBalance::class);
    }

    public function testModelHydratorAcceptsStringCodeAndNullData(): void
    {
        $response = new Response(200, ['Content-Type' => 'application/json'], <<<'JSON'
            {"success":true,"message":"success","error":false,"code":"200","data":null}
            JSON);

        $model = (new ModelHydrator())->hydrate($response, StubBalance::class);

        $this->assertInstanceOf(StubBalance::class, $model);
        $this->assertSame([], $model->received);
    }

    public function testModelHydratorRejectsNonJsonContentType(): void
    {
        $this->expectException(HydrationException::class);

        (new ModelHydrator())->hydrate(new Response(200, ['Content-Type' => 'text/html'], '<html/>'), StubBalance::class);
    }

    public function testModelHydratorRejectsInvalidJson(): void
    {
        $this->expectException(HydrationException::class);

        (new ModelHydrator())->hydrate(new Response(200, ['Content-Type' => 'application/json'], '{broken'), StubBalance::class);
    }

    public function testModelHydratorSupportsPlainDtoConstruction(): void
    {
        $response = new Response(200, ['Content-Type' => 'application/json'], <<<'JSON'
            {"success":true,"message":"success","data":{"a":1},"error":false,"code":200}
            JSON);

        $dto = (new ModelHydrator())->hydrate($response, StubDto::class);

        $this->assertInstanceOf(StubDto::class, $dto);
        $this->assertSame(['a' => 1], $dto->data);
    }

    public function testArrayHydratorReturnsUnwrappedData(): void
    {
        $response = new Response(200, ['Content-Type' => 'application/json'], <<<'JSON'
            {"success":true,"message":"success","data":{"balance":758.13},"error":false,"code":200}
            JSON);

        $this->assertSame(['balance' => 758.13], (new ArrayHydrator())->hydrate($response, StubBalance::class));
    }

    public function testArrayHydratorRejectsNonJsonContentType(): void
    {
        $this->expectException(HydrationException::class);

        (new ArrayHydrator())->hydrate(new Response(200, ['Content-Type' => 'text/html'], '<html/>'), StubBalance::class);
    }

    public function testNoopHydratorNeverHydrates(): void
    {
        $this->expectException(\LogicException::class);

        (new NoopHydrator())->hydrate(new Response(200), StubBalance::class);
    }

    /**
     * Live 403 shape: error carries the int, code carries "Forbidden".
     * Each fixture is served with its real live HTTP status.
     *
     * @dataProvider realErrorFixtureProvider
     */
    public function testRealErrorEnvelopesThrow(string $fixture, int $httpStatus, int $expectedCode): void
    {
        $json = file_get_contents(__DIR__ . '/../TestAssets/' . $fixture);
        $this->assertNotFalse($json);

        try {
            (new ModelHydrator())->hydrate(
                new Response($httpStatus, ['Content-Type' => 'application/json'], $json),
                StubBalance::class,
            );
            $this->fail("Expected EnviatodoException for {$fixture}.");
        } catch (EnviatodoException $e) {
            $this->assertSame($expectedCode, $e->getCode());
            $this->assertNotEmpty($e->getMessage());
        }
    }

    /**
     * @return array<string, array{string, int, int}>
     */
    public static function realErrorFixtureProvider(): array
    {
        return [
            'swapped 403' => ['error_missing_segment.json', 403, 403],
            'error:false cancel' => ['error_cancel_bogus.json', 200, 200],
            'error:false delete' => ['error_delete_missing.json', 200, 200],
            'string code 400' => ['error_filter_empty.json', 400, 400],
        ];
    }
}

final class StubBalance implements ApiResponse
{
    /** @var array<string, mixed> */
    public array $received;

    /** @param array<string, mixed> $data */
    private function __construct(array $data)
    {
        $this->received = $data;
    }

    /** @param array<string, mixed> $data */
    public static function create(array $data): self
    {
        return new self($data);
    }
}

final class StubDto
{
    /** @var array<string, mixed> */
    public array $data;

    /** @param array<string, mixed> $data */
    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
