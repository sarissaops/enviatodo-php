<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Model\Order;

use SarissaOps\Enviatodo\Model\ApiResponse;

final class Summary implements ApiResponse
{
    private float $subTotal;
    private float $tax;
    private float $total;

    private function __construct(float $subTotal, float $tax, float $total)
    {
        $this->subTotal = $subTotal;
        $this->tax = $tax;
        $this->total = $total;
    }

    /** @param array<array-key, mixed> $data */
    public static function create(array $data): static
    {
        return new self(
            self::amount($data['sub_total'] ?? 0),
            self::amount($data['tax'] ?? 0),
            self::amount($data['total'] ?? 0),
        );
    }

    public function getSubTotal(): float
    {
        return $this->subTotal;
    }

    public function getTax(): float
    {
        return $this->tax;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    private static function amount(mixed $value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }
}
