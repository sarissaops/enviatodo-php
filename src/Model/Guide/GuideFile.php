<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Model\Guide;

use SarissaOps\Enviatodo\Model\ApiResponse;

final class GuideFile implements ApiResponse
{
    private string $fileId;

    private function __construct(string $fileId)
    {
        $this->fileId = $fileId;
    }

    /** @param array<array-key, mixed> $data */
    public static function create(array $data): static
    {
        $fileId = $data['file_id'] ?? '';

        return new self(\is_string($fileId) || \is_int($fileId) ? (string) $fileId : '');
    }

    public function getFileId(): string
    {
        return $this->fileId;
    }
}
