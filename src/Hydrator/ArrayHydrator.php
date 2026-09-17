<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Hydrator;

use Psr\Http\Message\ResponseInterface;
use SarissaOps\Enviatodo\Exception\EnviatodoException;
use SarissaOps\Enviatodo\Exception\HydrationException;

/**
 * Returns the unwrapped envelope `data` as a plain array. Same envelope
 * checks as ModelHydrator, no object construction.
 */
final class ArrayHydrator implements Hydrator
{
    /**
     * @param class-string $class
     *
     * @return array<mixed>
     */
    public function hydrate(ResponseInterface $response, string $class)
    {
        $contentType = $response->getHeaderLine('Content-Type');
        if (0 !== strpos($contentType, 'application/json')) {
            throw new HydrationException('The ArrayHydrator cannot hydrate response with Content-Type: ' . $contentType);
        }

        try {
            $envelope = json_decode($response->getBody()->__toString(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new HydrationException(sprintf('Error (%d) when trying to json_decode response: %s', $exception->getCode(), $exception->getMessage()));
        }

        if (!\is_array($envelope)) {
            throw new HydrationException('The ArrayHydrator expected a JSON object envelope.');
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

        return \is_array($data) ? $data : [$data];
    }
}
