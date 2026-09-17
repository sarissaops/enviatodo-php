<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Api;

use Psr\Http\Message\ResponseInterface;
use SarissaOps\Enviatodo\Model\Catalog\Catalog;

class CatalogApi extends HttpApi
{
    /**
     * @return Catalog|array<mixed>|ResponseInterface
     */
    public function productTypes()
    {
        $response = $this->httpGet('Api/get_catalog/pts');
        $result = $this->hydrateResponse($response, Catalog::class);
        assert($result instanceof Catalog || \is_array($result) || $result instanceof ResponseInterface);

        return $result;
    }

    /**
     * @return Catalog|array<mixed>|ResponseInterface
     */
    public function packageTypes()
    {
        $response = $this->httpGet('Api/get_catalog/pkt');
        $result = $this->hydrateResponse($response, Catalog::class);
        assert($result instanceof Catalog || \is_array($result) || $result instanceof ResponseInterface);

        return $result;
    }
}
