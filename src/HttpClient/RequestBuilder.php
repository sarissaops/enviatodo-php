<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\HttpClient;

use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Builds PSR-7 requests. Array bodies are JSON-encoded (this API is
 * JSON-only); strings pass through untouched.
 */
class RequestBuilder
{
    private ?RequestFactoryInterface $requestFactory = null;
    private ?StreamFactoryInterface $streamFactory = null;

    /**
     * @param array<string, string> $headers
     * @param array<string, mixed>|string|null $body
     */
    public function create(string $method, string $uri, array $headers = [], $body = null): RequestInterface
    {
        if (\is_array($body)) {
            $stream = $this->getStreamFactory()->createStream((string) json_encode($body, JSON_THROW_ON_ERROR));
            $headers['Content-Type'] = 'application/json';

            return $this->createRequest($method, $uri, $headers, $stream);
        }

        return $this->createRequest(
            $method,
            $uri,
            $headers,
            $this->getStreamFactory()->createStream((string) $body),
        );
    }

    public function setRequestFactory(RequestFactoryInterface $requestFactory): self
    {
        $this->requestFactory = $requestFactory;

        return $this;
    }

    public function setStreamFactory(StreamFactoryInterface $streamFactory): self
    {
        $this->streamFactory = $streamFactory;

        return $this;
    }

    private function getRequestFactory(): RequestFactoryInterface
    {
        if (null === $this->requestFactory) {
            $this->requestFactory = Psr17FactoryDiscovery::findRequestFactory();
        }

        return $this->requestFactory;
    }

    private function getStreamFactory(): StreamFactoryInterface
    {
        if (null === $this->streamFactory) {
            $this->streamFactory = Psr17FactoryDiscovery::findStreamFactory();
        }

        return $this->streamFactory;
    }

    /**
     * @param array<string, string> $headers
     */
    private function createRequest(string $method, string $uri, array $headers, StreamInterface $stream): RequestInterface
    {
        $request = $this->getRequestFactory()->createRequest($method, $uri);
        $request = $request->withBody($stream);
        foreach ($headers as $name => $value) {
            $request = $request->withAddedHeader($name, $value);
        }

        return $request;
    }
}
