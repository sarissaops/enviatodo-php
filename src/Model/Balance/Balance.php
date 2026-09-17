<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Model\Balance;

use SarissaOps\Enviatodo\Model\ApiResponse;

final class Balance implements ApiResponse
{
    private float $balance;

    private function __construct(float $balance)
    {
        $this->balance = $balance;
    }

    /** @param array<array-key, mixed> $data */
    public static function create(array $data): static
    {
        $balance = $data['balance'] ?? 0.0;

        return new self((float) (is_numeric($balance) ? $balance : 0.0));
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    /**
     * The API reports no currency with the balance; accounts are MXN-only.
     */
    public function getCurrency(): string
    {
        return 'MXN';
    }
}
