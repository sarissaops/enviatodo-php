<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Hydrator;

use Psr\Http\Message\ResponseInterface;
use SarissaOps\Enviatodo\Exception\EnviatodoException;
use SarissaOps\Enviatodo\Exception\HydrationException;
use SarissaOps\Enviatodo\Model\ApiResponse;

/**
 * Default hydrator: checks the Enviatodo envelope, unwraps `data`, and builds
 * the model. Throws on `error: true` even when the HTTP status is 200.
 */
final class ModelHydrator implements Hydrator
{
    /**
     * @param class-string $class
     *
     * @return mixed
     */
    public function hydrate(ResponseInterface $response, string $class)
    {
        $contentType = $response->getHeaderLine('Content-Type');
        if (0 !== strpos($contentType, 'application/json')) {
            throw new HydrationException('The ModelHydrator cannot hydrate response with Content-Type: ' . $contentType);
        }

        try {
            $envelope = json_decode($response->getBody()->__toString(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new HydrationException(sprintf('Error (%d) when trying to json_decode response: %s', $exception->getCode(), $exception->getMessage()));
        }

        if (!\is_array($envelope)) {
            throw new HydrationException('The ModelHydrator expected a JSON object envelope.');
        }

        if (($envelope['error'] ?? false) === true || ($envelope['success'] ?? true) === false) {
            $message = $envelope['message'] ?? 'The Enviatodo API reported an error.';
            if (!\is_string($message)) {
                $message = 'The Enviatodo API reported an error.';
            }
            $code = $envelope['code'] ?? $response->getStatusCode();
            $code = is_numeric($code) ? (int) $code : $response->getStatusCode();

            throw new EnviatodoException($message, $code);
        }

        $data = $envelope['data'] ?? [];
        if (!\is_array($data)) {
            throw new HydrationException('The ModelHydrator expected envelope data to be an object or array.');
        }

        /** @var array<array-key, mixed> $payload */
        $payload = [];
        foreach ($data as $key => $value) {
            $payload[$key] = $value;
        }

        if (is_subclass_of($class, ApiResponse::class)) {
            $object = call_user_func([$class, 'create'], $payload);
        } else {
            $object = new $class($payload);
        }

        if (method_exists($object, 'setRawStream')) {
            $object->setRawStream($response->getBody());
        }
        if (method_exists($object, 'setStatusCode')) {
            $object->setStatusCode($response->getStatusCode());
        }
        if (method_exists($object, 'setHeaders')) {
            $object->setHeaders($response->getHeaders());
        }

        return $object;
    }
}
