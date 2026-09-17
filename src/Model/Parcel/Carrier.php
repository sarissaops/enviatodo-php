<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Model\Parcel;

use SarissaOps\Enviatodo\Model\ApiResponse;

final class Carrier implements ApiResponse
{
    private string $id;
    private string $tradeName;
    private string $source;
    private string $status;
    private string $providerId;
    private ?string $providerConfig;

    private function __construct(
        string $id,
        string $tradeName,
        string $source,
        string $status,
        string $providerId,
        ?string $providerConfig,
    ) {
        $this->id = $id;
        $this->tradeName = $tradeName;
        $this->source = $source;
        $this->status = $status;
        $this->providerId = $providerId;
        $this->providerConfig = $providerConfig;
    }

    /** @param array<array-key, mixed> $data */
    public static function create(array $data): static
    {
        $config = $data['provider_config'] ?? null;

        return new self(
            self::string($data['id'] ?? ''),
            self::string($data['trade_name'] ?? ''),
            self::string($data['source'] ?? ''),
            self::string($data['status'] ?? ''),
            self::string($data['provider_id'] ?? ''),
            \is_string($config) ? $config : null,
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTradeName(): string
    {
        return $this->tradeName;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getProviderId(): string
    {
        return $this->providerId;
    }

    public function getProviderConfig(): ?string
    {
        return $this->providerConfig;
    }

    private static function string(mixed $value): string
    {
        return \is_string($value) || \is_int($value) ? (string) $value : '';
    }
}
