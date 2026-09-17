<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Hydrator;

use Psr\Http\Message\ResponseInterface;

interface Hydrator
{
    /**
     * @param class-string $class
     *
     * @return mixed
     */
    public function hydrate(ResponseInterface $response, string $class);
}
