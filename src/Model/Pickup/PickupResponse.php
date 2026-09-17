<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Model\Pickup;

use SarissaOps\Enviatodo\Model\ApiResponse;

/**
 * Create/cancel acknowledgement: the object only exists when the envelope
 * reported success (envelope errors throw before it is built).
 */
final class PickupResponse implements ApiResponse
{
    /** @param array<array-key, mixed> $data */
    public static function create(array $data): static
    {
        return new self();
    }

    public function isCreated(): bool
    {
        return true;
    }
}
