<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Api;

use Psr\Http\Message\ResponseInterface;
use SarissaOps\Enviatodo\Assert;
use SarissaOps\Enviatodo\Model\Guide\GuideBinaries;
use SarissaOps\Enviatodo\Model\Guide\GuideFile;

class GuideApi extends HttpApi
{
    /**
     * @param list<int> $guideIds
     *
     * @return GuideFile|array<mixed>|ResponseInterface
     */
    public function download(array $guideIds)
    {
        Assert::isList($guideIds);
        Assert::notEmpty($guideIds, 'Download requires at least one guide id.');

        $response = $this->httpPost('Api/download_guides', ['guides' => $guideIds]);
        $result = $this->hydrateResponse($response, GuideFile::class);
        assert($result instanceof GuideFile || \is_array($result) || $result instanceof ResponseInterface);

        return $result;
    }

    /**
     * Per-file failures arrive inside invalid_files[] on HTTP 200 — inspect
     * them, they are not thrown.
     *
     * @param list<int> $guideIds
     *
     * @return GuideBinaries|array<mixed>|ResponseInterface
     */
    public function binaries(array $guideIds)
    {
        Assert::isList($guideIds);
        Assert::notEmpty($guideIds, 'Download requires at least one guide id.');

        $response = $this->httpPost('Api/download_guide_binaries', ['guides' => $guideIds]);
        $result = $this->hydrateResponse($response, GuideBinaries::class);
        assert($result instanceof GuideBinaries || \is_array($result) || $result instanceof ResponseInterface);

        return $result;
    }
}
