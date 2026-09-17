<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Model\Parcel;

use SarissaOps\Enviatodo\Model\ApiResponse;

/**
 * One nested service row inside a ParcelService parcel group.
 */
final class Service implements ApiResponse
{
    private string $providerServiceId;
    private string $label;
    private string $providerId;
    private string $viaTransport;
    private int $maxHeight;
    private int $maxWidth;
    private int $maxLength;
    private int $maxWeight;

    /**
     * @param array<string, mixed> $config
     */
    private function __construct(
        string $providerServiceId,
        string $label,
        string $providerId,
        string $viaTransport,
        array $config,
    ) {
        $this->providerServiceId = $providerServiceId;
        $this->label = $label;
        $this->providerId = $providerId;
        $this->viaTransport = $viaTransport;
        $this->maxHeight = self::limit($config['max_height'] ?? 0);
        $this->maxWidth = self::limit($config['max_width'] ?? 0);
        $this->maxLength = self::limit($config['max_length'] ?? 0);
        $this->maxWeight = self::limit($config['max_weight'] ?? 0);
    }

    /** @param array<array-key, mixed> $data */
    public static function create(array $data): static
    {
        $config = $data['config'] ?? [];
        if (!\is_array($config)) {
            $config = [];
        }
        $clean = [];
        foreach ($config as $key => $value) {
            $clean[(string) $key] = $value;
        }

        return new self(
            self::string($data['provider_service_id'] ?? ''),
            self::string($data['label'] ?? ''),
            self::string($data['provider_id'] ?? ''),
            self::string($data['via_transport'] ?? ''),
            $clean,
        );
    }

    public function getProviderServiceId(): string
    {
        return $this->providerServiceId;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getProviderId(): string
    {
        return $this->providerId;
    }

    public function getViaTransport(): string
    {
        return $this->viaTransport;
    }

    public function getMaxHeight(): int
    {
        return $this->maxHeight;
    }

    public function getMaxWidth(): int
    {
        return $this->maxWidth;
    }

    public function getMaxLength(): int
    {
        return $this->maxLength;
    }

    public function getMaxWeight(): int
    {
        return $this->maxWeight;
    }

    private static function string(mixed $value): string
    {
        return \is_string($value) || \is_int($value) ? (string) $value : '';
    }

    private static function limit(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }
}
