<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Hydrator;

use Psr\Http\Message\ResponseInterface;

/**
 * Never hydrates: the signal for HttpApi to return the raw PSR-7 response
 * with no envelope checks and no exceptions.
 */
final class NoopHydrator implements Hydrator
{
    /**
     * @param class-string $class
     *
     * @throws \LogicException
     */
    public function hydrate(ResponseInterface $response, string $class)
    {
        throw new \LogicException('The NoopHydrator should never be called.');
    }
}
