<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Model\Order;

use SarissaOps\Enviatodo\Model\ApiResponse;

/**
 * Cancel acknowledgement: the object only exists when the envelope reported
 * success (refunds land ~7 days later — this is not a synchronous refund).
 */
final class CancelResponse implements ApiResponse
{
    /** @param array<array-key, mixed> $data */
    public static function create(array $data): static
    {
        return new self();
    }

    public function isCancelled(): bool
    {
        return true;
    }
}
