<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Model;

/**
 * A value object built from the unwrapped `data` payload of an Enviatodo envelope.
 */
interface ApiResponse
{
    /**
     * @param array<array-key, mixed> $data Unwrapped envelope `data` (object-map or list).
     *
     * @return static
     */
    public static function create(array $data): static;
}
