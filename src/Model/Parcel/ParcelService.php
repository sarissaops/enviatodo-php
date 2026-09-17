<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Model\Parcel;

use SarissaOps\Enviatodo\Model\ApiResponse;

/**
 * One parcel group row: carrier + provider + its nested services.
 */
final class ParcelService implements ApiResponse
{
    private string $parcel;
    private string $providerId;

    /** @var list<Service> */
    private array $services;

    /**
     * @param list<Service> $services
     */
    private function __construct(string $parcel, string $providerId, array $services)
    {
        $this->parcel = $parcel;
        $this->providerId = $providerId;
        $this->services = $services;
    }

    /** @param array<array-key, mixed> $data */
    public static function create(array $data): static
    {
        $providerId = $data['provider_id'] ?? '';

        $services = [];
        foreach ((array) ($data['services'] ?? []) as $row) {
            if (!\is_array($row)) {
                continue;
            }
            /** @var array<array-key, mixed> $payload */
            $payload = [];
            foreach ($row as $key => $value) {
                $payload[$key] = $value;
            }
            $services[] = Service::create($payload);
        }

        return new self(
            self::string($data['parcel'] ?? ''),
            self::string($providerId),
            $services,
        );
    }

    public function getParcel(): string
    {
        return $this->parcel;
    }

    public function getProviderId(): string
    {
        return $this->providerId;
    }

    /** @return list<Service> */
    public function getServices(): array
    {
        return $this->services;
    }

    private static function string(mixed $value): string
    {
        return \is_string($value) || \is_int($value) ? (string) $value : '';
    }
}
