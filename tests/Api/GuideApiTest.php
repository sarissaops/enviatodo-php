<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\Api;

use SarissaOps\Enviatodo\Api\GuideApi;
use SarissaOps\Enviatodo\Exception\EnviatodoException;
use SarissaOps\Enviatodo\Exception\InvalidArgumentException;
use SarissaOps\Enviatodo\Model\Guide\GuideBinaries;
use SarissaOps\Enviatodo\Model\Guide\GuideFile;

final class GuideApiTest extends TestCase
{
    protected function apiClass(): string
    {
        return GuideApi::class;
    }

    public function testDownloadPostsGuideIds(): void
    {
        $api = $this->apiWithFixture('guide_file.json');
        assert($api instanceof GuideApi);

        $file = $api->download([2055848]);

        $this->assertSame('POST', $this->builder->method);
        $this->assertSame('Api/download_guides', $this->builder->uri);
        $this->assertSame(['guides' => [2055848]], $this->decodedBody());
        assert($file instanceof GuideFile);
        $this->assertSame('277564039300', $file->getFileId());
    }

    public function testBinariesSeparatesFilesFromInvalid(): void
    {
        $api = $this->apiWithFixture('guide_binaries.json');
        assert($api instanceof GuideApi);

        $binaries = $api->binaries([1309085, 1309086]);

        $this->assertSame('POST', $this->builder->method);
        $this->assertSame('Api/download_guide_binaries', $this->builder->uri);
        assert($binaries instanceof GuideBinaries);
        $files = $binaries->getFiles();
        $this->assertCount(1, $files);
        $this->assertSame(1309085, $files[0]->getGuideId());
        $this->assertSame('guia_1309085.pdf', $files[0]->getName());
        $this->assertSame('application/pdf', $files[0]->getMime());
        $invalid = $binaries->getInvalidFiles();
        $this->assertCount(1, $invalid);
        $this->assertSame(1309086, $invalid[0]->getGuideId());
    }

    public function testDownloadBogusIdYieldsEmptyFileId(): void
    {
        // Unknown guides answer HTTP 200 with null file_id.
        $api = $this->apiWithResponse(new \Nyholm\Psr7\Response(200, ['Content-Type' => 'application/json'], '{"success":true,"message":"success","data":{"file_id":null},"error":false,"code":200}'));
        assert($api instanceof GuideApi);

        $file = $api->download([999999999]);
        assert($file instanceof GuideFile);
        $this->assertSame('', $file->getFileId());
    }

    public function testDownloadRejectsEmptyIds(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $api = $this->apiWithFixture('guide_file.json');
        assert($api instanceof GuideApi);
        $api->download([]);
    }

    public function testBinariesRejectsEmptyIds(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $api = $this->apiWithFixture('guide_binaries.json');
        assert($api instanceof GuideApi);
        $api->binaries([]);
    }

    public function testThrowsOnEnvelopeError(): void
    {
        $this->expectException(EnviatodoException::class);

        $api = $this->apiWithResponse($this->errorEnvelope());
        assert($api instanceof GuideApi);
        $api->download([1]);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodedBody(): array
    {
        $body = $this->builder->body;
        if (\is_string($body)) {
            $decoded = json_decode($body, true);
            if (!\is_array($decoded)) {
                $this->fail('Expected the recorded request body to be a JSON object.');
            }
            $body = $decoded;
        }
        if (!\is_array($body)) {
            $this->fail('Expected the recorded request body to be an array.');
        }

        /** @var array<string, mixed> $clean */
        $clean = [];
        foreach ($body as $key => $value) {
            $clean[(string) $key] = $value;
        }

        return $clean;
    }
}
