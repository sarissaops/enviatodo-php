<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Model\Package;

use SarissaOps\Enviatodo\Model\ApiResponse;

/**
 * Save wrapper: add_package nests the record under a `package` key.
 */
final class SavedPackage implements ApiResponse
{
    private Package $package;

    private function __construct(Package $package)
    {
        $this->package = $package;
    }

    /** @param array<array-key, mixed> $data */
    public static function create(array $data): static
    {
        $record = $data['package'] ?? [];
        if (!\is_array($record)) {
            $record = [];
        }
        /** @var array<array-key, mixed> $payload */
        $payload = [];
        foreach ($record as $key => $value) {
            $payload[$key] = $value;
        }

        return new self(Package::create($payload));
    }

    public function getPackage(): Package
    {
        return $this->package;
    }
}
