<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Model\Pickup;

use SarissaOps\Enviatodo\Model\ApiResponse;

final class PickupOrder implements ApiResponse
{
    /** @var array<string, string> */
    private array $fields;

    /** @param array<string, string> $fields */
    private function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    /** @param array<array-key, mixed> $data */
    public static function create(array $data): static
    {
        $fields = [];
        foreach (self::KEYS as $key) {
            $fields[$key] = self::string($data[$key] ?? '');
        }

        return new self($fields);
    }

    public function getId(): string
    {
        return $this->fields['id'];
    }

    public function getTrackingId(): string
    {
        return $this->fields['tracking_id'];
    }

    public function getProviderName(): string
    {
        return $this->fields['provider_name'];
    }

    public function getProviderId(): string
    {
        return $this->fields['provider_id'];
    }

    public function getServiceName(): string
    {
        return $this->fields['service_name'];
    }

    public function getOrigin(): string
    {
        return $this->fields['origin'];
    }

    public function getCreatedAt(): string
    {
        return $this->fields['created_at'];
    }

    private const KEYS = [
        'id',
        'tracking_id',
        'provider_name',
        'provider_id',
        'service_name',
        'origin',
        'created_at',
    ];

    private static function string(mixed $value): string
    {
        return \is_string($value) || \is_int($value) ? (string) $value : '';
    }
}
