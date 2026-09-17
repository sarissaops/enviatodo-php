<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Api;

use Psr\Http\Message\ResponseInterface;
use SarissaOps\Enviatodo\Model\Parcel\Carrier;
use SarissaOps\Enviatodo\Model\Parcel\ParcelService;

class ParcelApi extends HttpApi
{
    /**
     * @return list<Carrier>|ResponseInterface
     */
    public function carriers()
    {
        return $this->hydrateList($this->httpGet('Api/get_parcel_service'), Carrier::class);
    }

    /**
     * @return list<ParcelService>|ResponseInterface
     */
    public function services()
    {
        return $this->hydrateList($this->httpGet('Api/provider_services'), ParcelService::class);
    }
}
