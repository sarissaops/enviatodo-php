<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Api;

use Psr\Http\Message\ResponseInterface;
use SarissaOps\Enviatodo\Model\Balance\Balance;

class BalanceApi extends HttpApi
{
    /**
     * @return Balance|array<mixed>|ResponseInterface
     */
    public function show()
    {
        $response = $this->httpGet('Api/get_client_balance');
        $result = $this->hydrateResponse($response, Balance::class);
        assert($result instanceof Balance || \is_array($result) || $result instanceof ResponseInterface);

        return $result;
    }
}
