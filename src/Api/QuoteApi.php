<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Api;

use Psr\Http\Message\ResponseInterface;
use SarissaOps\Enviatodo\Assert;
use SarissaOps\Enviatodo\Exception\InvalidArgumentException;
use SarissaOps\Enviatodo\Model\Quote\Quote;

class QuoteApi extends HttpApi
{
    private const REQUIRED_PACKAGE_KEYS = [
        'product_type',
        'unit_type',
        'package_content',
        'amount_pkg',
        'height',
        'width',
        'length',
        'weight',
        'real_weight',
        'volumetric_weight',
        'bill_weight',
        'default_pkg',
    ];

    /**
     * Quote one carrier service. The package object must carry the weight keys
     * or the API answers HTTP 200 with error:true.
     *
     * @param array<string, mixed> $quote
     *
     * @return Quote|array<mixed>|ResponseInterface
     */
    public function byService(array $quote, int $providerServiceId)
    {
        $quotes = $this->quotes($quote);
        $quotes['provider_service_id'] = $providerServiceId;

        return $this->rates($quotes);
    }

    /**
     * @param array<string, mixed> $quote
     *
     * @return Quote|array<mixed>|ResponseInterface
     */
    public function byProvider(array $quote, int $providerId)
    {
        $quotes = $this->quotes($quote);
        $quotes['provider_id'] = $providerId;

        return $this->rates($quotes);
    }

    /**
     * @param array<string, mixed> $quote
     *
     * @return Quote|array<mixed>|ResponseInterface
     */
    public function all(array $quote)
    {
        return $this->rates($this->quotes($quote));
    }

    /**
     * @param array<string, mixed> $quote
     *
     * @return array<string, mixed>
     */
    private function quotes(array $quote): array
    {
        Assert::keyExists($quote, 'origin', 'Quote requires an origin address.');
        Assert::keyExists($quote, 'destination', 'Quote requires a destination address.');
        Assert::keyExists($quote, 'package', 'Quote requires a package.');

        $package = $quote['package'];
        if (!\is_array($package)) {
            throw new InvalidArgumentException('Quote package must be an array.');
        }
        foreach (self::REQUIRED_PACKAGE_KEYS as $key) {
            Assert::keyExists($package, $key, sprintf('Quote package requires key "%s".', $key));
        }

        $quotes = $quote;
        unset($quotes['provider_id'], $quotes['provider_service_id']);

        return $quotes;
    }

    /**
     * @param array<string, mixed> $quotes
     *
     * @return Quote|array<mixed>|ResponseInterface
     */
    private function rates(array $quotes)
    {
        $response = $this->httpPost('Api/rates_client', ['type' => 'order', 'quotes' => $quotes]);
        $result = $this->hydrateResponse($response, Quote::class);
        assert($result instanceof Quote || \is_array($result) || $result instanceof ResponseInterface);

        return $result;
    }
}
