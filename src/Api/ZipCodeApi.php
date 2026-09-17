<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Api;

use Psr\Http\Message\ResponseInterface;
use SarissaOps\Enviatodo\Assert;
use SarissaOps\Enviatodo\Model\ZipCode\ZipCode;

class ZipCodeApi extends HttpApi
{
    /**
     * @return ZipCode|array<mixed>|ResponseInterface
     */
    public function show(string $zip)
    {
        Assert::stringNotEmpty($zip);

        $response = $this->httpGet(sprintf('Api/get_zip_code/%s', $zip));
        $result = $this->hydrateResponse($response, ZipCode::class);
        assert($result instanceof ZipCode || \is_array($result) || $result instanceof ResponseInterface);

        return $result;
    }
}
