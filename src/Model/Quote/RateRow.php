<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Model\Quote;

use SarissaOps\Enviatodo\Model\ApiResponse;

final class RateRow implements ApiResponse
{
    private string $providerId;
    private string $serviceName;
    private string $providerServiceId;
    private string $viaTransport;
    private string $rateType;
    private string $type;
    private string $deliveryMode;
    private string $estimatedDate;
    private string $providerZone;
    private string $providerName;
    private bool $status;
    private int $priority;

    /** @var array<array-key, mixed> */
    private array $offer;

    /** @var list<Charge> */
    private array $charges;

    /** @var array<int, mixed> */
    private array $detailCharges;

    /**
     * @param array<array-key, mixed> $offer
     * @param list<Charge> $charges
     * @param array<int, mixed> $detailCharges
     */
    private function __construct(
        string $providerId,
        string $serviceName,
        string $providerServiceId,
        string $viaTransport,
        string $rateType,
        string $type,
        string $deliveryMode,
        string $estimatedDate,
        string $providerZone,
        string $providerName,
        bool $status,
        int $priority,
        array $offer,
        array $charges,
        array $detailCharges,
    ) {
        $this->providerId = $providerId;
        $this->serviceName = $serviceName;
        $this->providerServiceId = $providerServiceId;
        $this->viaTransport = $viaTransport;
        $this->rateType = $rateType;
        $this->type = $type;
        $this->deliveryMode = $deliveryMode;
        $this->estimatedDate = $estimatedDate;
        $this->providerZone = $providerZone;
        $this->providerName = $providerName;
        $this->status = $status;
        $this->priority = $priority;
        $this->offer = $offer;
        $this->charges = $charges;
        $this->detailCharges = $detailCharges;
    }

    /** @param array<array-key, mixed> $data */
    public static function create(array $data): static
    {
        $charges = [];
        foreach ((array) ($data['charges'] ?? []) as $row) {
            if (!\is_array($row)) {
                continue;
            }
            $charges[] = Charge::create(self::payload($row));
        }

        $details = [];
        foreach ((array) ($data['detail_charges'] ?? []) as $row) {
            $details[] = $row;
        }

        $offer = $data['offer'] ?? [];
        if (!\is_array($offer)) {
            $offer = [];
        }
        /** @var array<array-key, mixed> $offerPayload */
        $offerPayload = [];
        foreach ($offer as $key => $value) {
            $offerPayload[$key] = $value;
        }

        return new self(
            self::string($data['provider_id'] ?? ''),
            self::string($data['service_name'] ?? ''),
            self::string($data['provider_service_id'] ?? ''),
            self::string($data['via_transport'] ?? ''),
            self::string($data['rate_type'] ?? ''),
            self::string($data['type'] ?? ''),
            self::string($data['delivery_mode'] ?? ''),
            self::string($data['estimated_date'] ?? ''),
            self::string($data['provider_zone'] ?? ''),
            self::string($data['provider_name'] ?? ''),
            ($data['status'] ?? false) === true,
            isset($data['priority']) && is_numeric($data['priority']) ? (int) $data['priority'] : 0,
            $offerPayload,
            $charges,
            $details,
        );
    }

    public function getProviderId(): string
    {
        return $this->providerId;
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    public function getProviderServiceId(): string
    {
        return $this->providerServiceId;
    }

    public function getViaTransport(): string
    {
        return $this->viaTransport;
    }

    public function getRateType(): string
    {
        return $this->rateType;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDeliveryMode(): string
    {
        return $this->deliveryMode;
    }

    public function getEstimatedDate(): string
    {
        return $this->estimatedDate;
    }

    public function getProviderZone(): string
    {
        return $this->providerZone;
    }

    public function getProviderName(): string
    {
        return $this->providerName;
    }

    public function isAvailable(): bool
    {
        return $this->status;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    /** @return array<array-key, mixed> */
    public function getOffer(): array
    {
        return $this->offer;
    }

    /** @return list<Charge> */
    public function getCharges(): array
    {
        return $this->charges;
    }

    /** @return array<int, mixed> */
    public function getDetailCharges(): array
    {
        return $this->detailCharges;
    }

    /**
     * Currency is only reported on detail_charges rows, not on charges.
     */
    public function getCurrency(): ?string
    {
        foreach ($this->detailCharges as $row) {
            if (\is_array($row) && isset($row['currency']) && \is_string($row['currency']) && '' !== $row['currency']) {
                return $row['currency'];
            }
        }

        return null;
    }

    /**
     * @param array<mixed> $row
     *
     * @return array<array-key, mixed>
     */
    private static function payload(array $row): array
    {
        /** @var array<array-key, mixed> $payload */
        $payload = [];
        foreach ($row as $key => $value) {
            $payload[$key] = $value;
        }

        return $payload;
    }

    private static function string(mixed $value): string
    {
        return \is_string($value) || \is_int($value) ? (string) $value : '';
    }
}
