<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Model\Order;

use SarissaOps\Enviatodo\Model\ApiResponse;

final class Guide implements ApiResponse
{
    private string $id;
    private bool $insured;
    private string $trackingId;
    private string $providerId;

    private function __construct(string $id, bool $insured, string $trackingId, string $providerId)
    {
        $this->id = $id;
        $this->insured = $insured;
        $this->trackingId = $trackingId;
        $this->providerId = $providerId;
    }

    /** @param array<array-key, mixed> $data */
    public static function create(array $data): static
    {
        return new self(
            self::string($data['id'] ?? ''),
            ($data['insured'] ?? false) === true,
            self::string($data['tracking_id'] ?? ''),
            self::string($data['provider_id'] ?? ''),
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function isInsured(): bool
    {
        return $this->insured;
    }

    public function getTrackingId(): string
    {
        return $this->trackingId;
    }

    public function getProviderId(): string
    {
        return $this->providerId;
    }

    private static function string(mixed $value): string
    {
        return \is_string($value) || \is_int($value) ? (string) $value : '';
    }
}
