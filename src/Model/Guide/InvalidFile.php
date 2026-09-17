<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Model\Guide;

use SarissaOps\Enviatodo\Model\ApiResponse;

final class InvalidFile implements ApiResponse
{
    private int $guideId;
    private string $reason;
    private string $binary;

    private function __construct(int $guideId, string $reason, string $binary)
    {
        $this->guideId = $guideId;
        $this->reason = $reason;
        $this->binary = $binary;
    }

    /** @param array<array-key, mixed> $data */
    public static function create(array $data): static
    {
        $guideId = $data['guide_id'] ?? 0;

        return new self(
            is_numeric($guideId) ? (int) $guideId : 0,
            self::string($data['reason'] ?? ''),
            self::string($data['binary'] ?? ''),
        );
    }

    public function getGuideId(): int
    {
        return $this->guideId;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getBinary(): string
    {
        return $this->binary;
    }

    private static function string(mixed $value): string
    {
        return \is_string($value) ? $value : '';
    }
}
