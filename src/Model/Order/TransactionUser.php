<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Model\Order;

use SarissaOps\Enviatodo\Model\ApiResponse;

final class TransactionUser implements ApiResponse
{
    private string $userId;
    private string $fullName;

    private function __construct(string $userId, string $fullName)
    {
        $this->userId = $userId;
        $this->fullName = $fullName;
    }

    /** @param array<array-key, mixed> $data */
    public static function create(array $data): static
    {
        return new self(
            self::string($data['user_id'] ?? ''),
            self::string($data['full_name_user'] ?? ''),
        );
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    private static function string(mixed $value): string
    {
        return \is_string($value) || \is_int($value) ? (string) $value : '';
    }
}
