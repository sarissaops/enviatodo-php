<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\HttpClient\Plugin;

use Http\Client\Common\Plugin\Journal;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Remembers the last response, exposed via Enviatodo::getLastResponse().
 */
final class History implements Journal
{
    private ?ResponseInterface $lastResponse = null;

    public function getLastResponse(): ?ResponseInterface
    {
        return $this->lastResponse;
    }

    public function addSuccess(RequestInterface $request, ResponseInterface $response): void
    {
        $this->lastResponse = $response;
    }

    public function addFailure(RequestInterface $request, ClientExceptionInterface $exception): void {}
}
