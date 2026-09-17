<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Model\Address;

use SarissaOps\Enviatodo\Model\ApiResponse;

/**
 * Save wrapper: add_address nests the record under an `address` key.
 */
final class SavedAddress implements ApiResponse
{
    private Address $address;

    private function __construct(Address $address)
    {
        $this->address = $address;
    }

    /** @param array<array-key, mixed> $data */
    public static function create(array $data): static
    {
        $record = $data['address'] ?? [];
        if (!\is_array($record)) {
            $record = [];
        }
        /** @var array<array-key, mixed> $payload */
        $payload = [];
        foreach ($record as $key => $value) {
            $payload[$key] = $value;
        }

        return new self(Address::create($payload));
    }

    public function getAddress(): Address
    {
        return $this->address;
    }
}
