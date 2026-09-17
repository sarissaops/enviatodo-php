<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Exception;

use Psr\Http\Message\ResponseInterface;
use SarissaOps\Enviatodo\Exception;

final class HttpClientException extends \RuntimeException implements Exception
{
    private ?ResponseInterface $response;

    /** @var array<string, mixed> */
    private array $responseBody = [];

    private int $responseCode;

    public function __construct(string $message, int $code, ?ResponseInterface $response = null)
    {
        parent::__construct($message, $code);

        $this->response = $response;
        $this->responseCode = $response?->getStatusCode() ?? $code;
        if (null !== $response) {
            $rawBody = $response->getBody()->__toString();
            if (0 !== strpos($response->getHeaderLine('Content-Type'), 'application/json')) {
                $this->responseBody['message'] = $rawBody;
            } else {
                $decoded = json_decode($rawBody, true);
                if (\is_array($decoded)) {
                    $stringKeyed = [];
                    foreach ($decoded as $key => $value) {
                        $stringKeyed[(string) $key] = $value;
                    }
                    $this->responseBody = $stringKeyed;
                } else {
                    $this->responseBody = ['message' => $rawBody];
                }
            }
        }
    }

    private static function envelopeMessage(string $body, string $contentType): string
    {
        if (0 !== strpos($contentType, 'application/json')) {
            return $body;
        }

        $decoded = json_decode($body, true);
        if (\is_array($decoded) && isset($decoded['message']) && \is_string($decoded['message'])) {
            return $decoded['message'];
        }

        return $body;
    }

    public static function badRequest(ResponseInterface $response): self
    {
        $validationMessage = self::envelopeMessage(
            $response->getBody()->__toString(),
            $response->getHeaderLine('Content-Type'),
        );

        return new self(
            sprintf("The parameters passed to the API were invalid. Check your inputs!\n\n%s", $validationMessage),
            400,
            $response,
        );
    }

    public static function unauthorized(ResponseInterface $response): self
    {
        return new self('Your credentials are incorrect.', 401, $response);
    }

    public static function requestFailed(ResponseInterface $response): self
    {
        return new self('Parameters were valid but request failed. Try again.', 402, $response);
    }

    public static function forbidden(ResponseInterface $response): self
    {
        $validationMessage = self::envelopeMessage(
            $response->getBody()->__toString(),
            $response->getHeaderLine('Content-Type'),
        );

        return new self(sprintf("Forbidden!\n\n%s", $validationMessage), 403, $response);
    }

    public static function notFound(ResponseInterface $response): self
    {
        $defaultMessage = 'The endpoint you have tried to access does not exist. Check the URL and resource id.';
        try {
            $serverMessage = json_decode($response->getBody()->__toString(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $serverMessage = [];
        }

        $message = $defaultMessage;
        if (\is_array($serverMessage) && isset($serverMessage['message']) && \is_string($serverMessage['message'])) {
            $message = $serverMessage['message'];
        }

        return new self($message, 404, $response);
    }

    public static function conflict(ResponseInterface $response): self
    {
        return new self('Request conflicts with current state of the target resource.', 409, $response);
    }

    public static function payloadTooLarge(ResponseInterface $response): self
    {
        return new self('Payload too large.', 413, $response);
    }

    public static function tooManyRequests(ResponseInterface $response): self
    {
        return new self('Too many requests.', 429, $response);
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    /** @return array<string, mixed> */
    public function getResponseBody(): array
    {
        return $this->responseBody;
    }

    public function getResponseCode(): int
    {
        return $this->responseCode;
    }
}
