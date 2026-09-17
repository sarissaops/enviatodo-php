<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\Api;

use SarissaOps\Enviatodo\Api\ZipCodeApi;
use SarissaOps\Enviatodo\Exception\EnviatodoException;
use SarissaOps\Enviatodo\Exception\InvalidArgumentException;
use SarissaOps\Enviatodo\Model\ZipCode\ZipCode;

final class ZipCodeApiTest extends TestCase
{
    protected function apiClass(): string
    {
        return ZipCodeApi::class;
    }

    public function testShowRequestsZipEndpoint(): void
    {
        $api = $this->apiWithFixture('zip_code.json');

        assert($api instanceof ZipCodeApi);

        $zip = $api->show('64764');

        $this->assertSame('GET', $this->builder->method);
        $this->assertSame('Api/get_zip_code/64764', $this->builder->uri);
        $this->assertInstanceOf(ZipCode::class, $zip);
        $this->assertSame('catalog', $zip->getComponent());
        $this->assertSame('zip_code', $zip->getType());
        $this->assertCount(2, $zip->getItems());
        $this->assertSame('Los Rosales', $zip->getItems()[0]['suburb_name']);
    }

    public function testShowRejectsEmptyZip(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $api = $this->apiWithFixture('zip_code.json');
        assert($api instanceof ZipCodeApi);
        $api->show('');
    }

    public function testBadZipLiveShape(): void
    {
        $api = $this->apiWithFixture('error_bad_zip.json');
        assert($api instanceof ZipCodeApi);

        try {
            $api->show('00000');
            $this->fail('Expected EnviatodoException.');
        } catch (EnviatodoException $e) {
            $this->assertSame(400, $e->getCode());
            $this->assertStringContainsString('zip_code', $e->getMessage());
        }
    }

    public function testShowThrowsOnEnvelopeError(): void
    {
        $this->expectException(EnviatodoException::class);

        $api = $this->apiWithResponse($this->errorEnvelope());
        assert($api instanceof ZipCodeApi);
        $api->show('00000');
    }
}
