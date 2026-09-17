<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Api;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use SarissaOps\Enviatodo\Exception\HttpClientException;
use SarissaOps\Enviatodo\Exception\HttpServerException;
use SarissaOps\Enviatodo\Exception\HydrationException;
use SarissaOps\Enviatodo\Exception\UnknownErrorException;
use SarissaOps\Enviatodo\HttpClient\RequestBuilder;
use SarissaOps\Enviatodo\Hydrator\ArrayHydrator;
use SarissaOps\Enviatodo\Hydrator\Hydrator;
use SarissaOps\Enviatodo\Hydrator\NoopHydrator;
use SarissaOps\Enviatodo\Model\ApiResponse;
use Psr\Http\Client as Psr18;

abstract class HttpApi
{
    protected ClientInterface $httpClient;

    protected ?Hydrator $hydrator = null;

    protected RequestBuilder $requestBuilder;

    public function __construct(ClientInterface $httpClient, RequestBuilder $requestBuilder, Hydrator $hydrator)
    {
        $this->httpClient = $httpClient;
        $this->requestBuilder = $requestBuilder;
        if (!$hydrator instanceof NoopHydrator) {
            $this->hydrator = $hydrator;
        }
    }

    /**
     * @param class-string $className
     *
     * @return mixed|ResponseInterface
     */
    protected function hydrateResponse(ResponseInterface $response, string $className)
    {
        $hydrator = $this->hydrator;
        if (null === $hydrator) {
            return $response;
        }

        if (!\in_array($response->getStatusCode(), [200, 201, 202], true)) {
            $this->handleErrors($response);
        }

        return $hydrator->hydrate($response, $className);
    }

    /**
     * Hydrate a list endpoint: every row of the unwrapped `data` array becomes
     * an instance of $class. Native Model[] — no wrapper object.
     *
     * @template T of ApiResponse
     *
     * @param class-string<T> $class
     *
     * @return list<T>|ResponseInterface
     */
    protected function hydrateList(ResponseInterface $response, string $class)
    {
        if (null === $this->hydrator) {
            return $response;
        }

        if (!\in_array($response->getStatusCode(), [200, 201, 202], true)) {
            $this->handleErrors($response);
        }

        $data = (new ArrayHydrator())->hydrate($response, $class);
        if (!array_is_list($data)) {
            throw new HydrationException(sprintf('Expected a list payload for %s.', $class));
        }

        $models = [];
        foreach ($data as $row) {
            if (!\is_array($row)) {
                throw new HydrationException(sprintf('Expected list rows to be objects for %s.', $class));
            }
            /** @var array<array-key, mixed> $payload */
            $payload = [];
            foreach ($row as $key => $value) {
                $payload[$key] = $value;
            }
            $models[] = $class::create($payload);
        }

        return $models;
    }

    protected function handleErrors(ResponseInterface $response): void
    {
        switch ($response->getStatusCode()) {
            case 400:
                throw HttpClientException::badRequest($response);
            case 401:
                throw HttpClientException::unauthorized($response);
            case 402:
                throw HttpClientException::requestFailed($response);
            case 403:
                throw HttpClientException::forbidden($response);
            case 404:
                throw HttpClientException::notFound($response);
            case 409:
                throw HttpClientException::conflict($response);
            case 413:
                throw HttpClientException::payloadTooLarge($response);
            case 429:
                throw HttpClientException::tooManyRequests($response);
            default:
                $statusCode = $response->getStatusCode();
                if ($statusCode >= 500 && $statusCode < 600) {
                    throw HttpServerException::serverError($statusCode);
                }

                throw new UnknownErrorException();
        }
    }

    /**
     * @param array<string, string> $parameters
     * @param array<string, string> $requestHeaders
     */
    protected function httpGet(string $path, array $parameters = [], array $requestHeaders = []): ResponseInterface
    {
        if (\count($parameters) > 0) {
            $path .= '?' . http_build_query($parameters);
        }

        try {
            $response = $this->httpClient->sendRequest(
                $this->requestBuilder->create('GET', $path, $requestHeaders),
            );
        } catch (Psr18\NetworkExceptionInterface $e) {
            throw HttpServerException::networkError($e);
        }

        return $response;
    }

    /**
     * @param array<string, mixed> $parameters
     * @param array<string, string> $requestHeaders
     */
    protected function httpPost(string $path, array $parameters = [], array $requestHeaders = []): ResponseInterface
    {
        return $this->httpPostRaw($path, $parameters, $requestHeaders);
    }

    /**
     * @param array<string, mixed>|string $body
     * @param array<string, string> $requestHeaders
     */
    protected function httpPostRaw(string $path, $body, array $requestHeaders = []): ResponseInterface
    {
        try {
            $response = $this->httpClient->sendRequest(
                $this->requestBuilder->create('POST', $path, $requestHeaders, $body),
            );
        } catch (Psr18\NetworkExceptionInterface $e) {
            throw HttpServerException::networkError($e);
        }

        return $response;
    }

    /**
     * @param array<string, mixed> $parameters
     * @param array<string, string> $requestHeaders
     */
    protected function httpPut(string $path, array $parameters = [], array $requestHeaders = []): ResponseInterface
    {
        try {
            $response = $this->httpClient->sendRequest(
                $this->requestBuilder->create('PUT', $path, $requestHeaders, $parameters),
            );
        } catch (Psr18\NetworkExceptionInterface $e) {
            throw HttpServerException::networkError($e);
        }

        return $response;
    }

    /**
     * @param array<string, mixed> $parameters
     * @param array<string, string> $requestHeaders
     */
    protected function httpPatch(string $path, array $parameters = [], array $requestHeaders = []): ResponseInterface
    {
        try {
            $response = $this->httpClient->sendRequest(
                $this->requestBuilder->create('PATCH', $path, $requestHeaders, $parameters),
            );
        } catch (Psr18\NetworkExceptionInterface $e) {
            throw HttpServerException::networkError($e);
        }

        return $response;
    }

    /**
     * @param array<string, mixed> $parameters
     * @param array<string, string> $requestHeaders
     */
    protected function httpDelete(string $path, array $parameters = [], array $requestHeaders = []): ResponseInterface
    {
        try {
            $response = $this->httpClient->sendRequest(
                $this->requestBuilder->create('DELETE', $path, $requestHeaders, $parameters),
            );
        } catch (Psr18\NetworkExceptionInterface $e) {
            throw HttpServerException::networkError($e);
        }

        return $response;
    }
}
