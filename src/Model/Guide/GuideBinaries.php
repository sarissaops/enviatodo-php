<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Model\Guide;

use SarissaOps\Enviatodo\Model\ApiResponse;

final class GuideBinaries implements ApiResponse
{
    /** @var list<GuideBinary> */
    private array $files;

    /** @var list<InvalidFile> */
    private array $invalidFiles;

    /**
     * @param list<GuideBinary> $files
     * @param list<InvalidFile> $invalidFiles
     */
    private function __construct(array $files, array $invalidFiles)
    {
        $this->files = $files;
        $this->invalidFiles = $invalidFiles;
    }

    /** @param array<array-key, mixed> $data */
    public static function create(array $data): static
    {
        return new self(
            self::rows($data['files'] ?? [], GuideBinary::class),
            self::rows($data['invalid_files'] ?? [], InvalidFile::class),
        );
    }

    /** @return list<GuideBinary> */
    public function getFiles(): array
    {
        return $this->files;
    }

    /** @return list<InvalidFile> */
    public function getInvalidFiles(): array
    {
        return $this->invalidFiles;
    }

    /**
     * @template T of ApiResponse
     *
     * @param mixed $rows
     * @param class-string<T> $class
     *
     * @return list<T>
     */
    private static function rows(mixed $rows, string $class): array
    {
        if (!\is_array($rows)) {
            return [];
        }
        $models = [];
        foreach ($rows as $row) {
            if (!\is_array($row)) {
                continue;
            }
            /** @var array<array-key, mixed> $payload */
            $payload = [];
            foreach ($row as $key => $value) {
                $payload[$key] = $value;
            }
            $models[] = $class::create($payload);
        }

        return $models;
    }
}
