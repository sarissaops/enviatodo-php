<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Model\Quote;

use SarissaOps\Enviatodo\Model\ApiResponse;

final class Quote implements ApiResponse
{
    private string $transactionType;
    private string $transactionTimestamp;
    private string $transactionUuid;
    private int $packageQuantity;

    /** @var list<RateRow> */
    private array $rates;

    /**
     * @param list<RateRow> $rates
     */
    private function __construct(
        string $transactionType,
        string $transactionTimestamp,
        string $transactionUuid,
        int $packageQuantity,
        array $rates,
    ) {
        $this->transactionType = $transactionType;
        $this->transactionTimestamp = $transactionTimestamp;
        $this->transactionUuid = $transactionUuid;
        $this->packageQuantity = $packageQuantity;
        $this->rates = $rates;
    }

    /** @param array<array-key, mixed> $data */
    public static function create(array $data): static
    {
        $transaction = $data['transaction'] ?? [];
        if (!\is_array($transaction)) {
            $transaction = [];
        }
        $packages = $data['packages'] ?? [];
        if (!\is_array($packages)) {
            $packages = [];
        }

        $rates = [];
        foreach ((array) ($data['rates'] ?? []) as $row) {
            if (!\is_array($row)) {
                continue;
            }
            /** @var array<array-key, mixed> $payload */
            $payload = [];
            foreach ($row as $key => $value) {
                $payload[$key] = $value;
            }
            $rates[] = RateRow::create($payload);
        }

        $quantity = $packages['quantity'] ?? 0;

        return new self(
            self::string($transaction['type'] ?? ''),
            self::string($transaction['timestamp'] ?? ''),
            self::string($transaction['uuid'] ?? ''),
            is_numeric($quantity) ? (int) $quantity : 0,
            $rates,
        );
    }

    public function getTransactionType(): string
    {
        return $this->transactionType;
    }

    public function getTransactionTimestamp(): string
    {
        return $this->transactionTimestamp;
    }

    public function getTransactionUuid(): string
    {
        return $this->transactionUuid;
    }

    public function getPackageQuantity(): int
    {
        return $this->packageQuantity;
    }

    /** @return list<RateRow> */
    public function getRates(): array
    {
        return $this->rates;
    }

    private static function string(mixed $value): string
    {
        return \is_string($value) ? $value : '';
    }
}
