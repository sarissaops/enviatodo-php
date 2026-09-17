<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Model\Order;

use SarissaOps\Enviatodo\Model\ApiResponse;

final class Order implements ApiResponse
{
    private string $trxId;
    private string $shippingType;
    private string $statusName;
    private string $createdAt;
    private string $trxUuid;
    private string $trxType;
    private string $userId;

    /** @var array<string, mixed> */
    private array $detail;

    /**
     * @param array<string, mixed> $detail
     */
    private function __construct(
        string $trxId,
        string $shippingType,
        string $statusName,
        string $createdAt,
        string $trxUuid,
        string $trxType,
        string $userId,
        array $detail,
    ) {
        $this->trxId = $trxId;
        $this->shippingType = $shippingType;
        $this->statusName = $statusName;
        $this->createdAt = $createdAt;
        $this->trxUuid = $trxUuid;
        $this->trxType = $trxType;
        $this->userId = $userId;
        $this->detail = $detail;
    }

    /** @param array<array-key, mixed> $data */
    public static function create(array $data): static
    {
        $detail = $data['order_detail'] ?? [];
        if (!\is_array($detail)) {
            $detail = [];
        }
        /** @var array<string, mixed> $clean */
        $clean = [];
        foreach ($detail as $key => $value) {
            $clean[(string) $key] = \is_array($value) ? self::section($value) : [];
        }
        foreach (['address', 'package', 'recipient', 'order', 'guide', 'fulfilment'] as $section) {
            $clean[$section] ??= [];
        }

        return new self(
            self::string($data['trx_id'] ?? ''),
            self::string($data['shipping_type'] ?? ''),
            self::string($data['status_name'] ?? ''),
            self::string($data['created_at'] ?? ''),
            self::string($data['trx_uuid'] ?? ''),
            self::string($data['trx_type'] ?? ''),
            self::string($data['user_id'] ?? ''),
            $clean,
        );
    }

    public function getTrxId(): string
    {
        return $this->trxId;
    }

    public function getShippingType(): string
    {
        return $this->shippingType;
    }

    public function getStatusName(): string
    {
        return $this->statusName;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getTrxUuid(): string
    {
        return $this->trxUuid;
    }

    public function getTrxType(): string
    {
        return $this->trxType;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    /** @return array<string, mixed> */
    public function getDetail(): array
    {
        return $this->detail;
    }

    /**
     * @param array<mixed> $section
     *
     * @return array<string, mixed>
     */
    private static function section(array $section): array
    {
        /** @var array<string, mixed> $clean */
        $clean = [];
        foreach ($section as $key => $value) {
            $clean[(string) $key] = $value;
        }

        return $clean;
    }

    private static function string(mixed $value): string
    {
        return \is_string($value) || \is_int($value) ? (string) $value : '';
    }
}
