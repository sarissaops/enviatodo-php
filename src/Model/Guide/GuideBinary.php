<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Model\Guide;

use SarissaOps\Enviatodo\Model\ApiResponse;

final class GuideBinary implements ApiResponse
{
    private int $guideId;
    private string $name;
    private string $binary;
    private string $mime;

    private function __construct(int $guideId, string $name, string $binary, string $mime)
    {
        $this->guideId = $guideId;
        $this->name = $name;
        $this->binary = $binary;
        $this->mime = $mime;
    }

    /** @param array<array-key, mixed> $data */
    public static function create(array $data): static
    {
        $guideId = $data['guide_id'] ?? 0;

        return new self(
            is_numeric($guideId) ? (int) $guideId : 0,
            self::string($data['name'] ?? ''),
            self::string($data['binary'] ?? ''),
            self::string($data['mime'] ?? ''),
        );
    }

    public function getGuideId(): int
    {
        return $this->guideId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBinary(): string
    {
        return $this->binary;
    }

    public function getMime(): string
    {
        return $this->mime;
    }

    private static function string(mixed $value): string
    {
        return \is_string($value) ? $value : '';
    }
}
