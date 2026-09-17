<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Model\Address;

use SarissaOps\Enviatodo\Model\ApiResponse;

/**
 * Delete acknowledgement: the object only exists when the envelope reported
 * success (envelope errors throw before it is built).
 */
final class DeleteResponse implements ApiResponse
{
    /** @param array<array-key, mixed> $data */
    public static function create(array $data): static
    {
        return new self();
    }

    public function isDeleted(): bool
    {
        return true;
    }
}
