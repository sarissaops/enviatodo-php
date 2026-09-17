<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Model\ZipCode;

use SarissaOps\Enviatodo\Model\ApiResponse;

final class ZipCode implements ApiResponse
{
    private string $component;

    private string $type;

    /** @var array<int, array<string, mixed>> */
    private array $items;

    /**
     * @param array<int, array<string, mixed>> $items
     */
    private function __construct(string $component, string $type, array $items)
    {
        $this->component = $component;
        $this->type = $type;
        $this->items = $items;
    }

    /** @param array<array-key, mixed> $data */
    public static function create(array $data): static
    {
        $component = $data['component'] ?? '';
        $type = $data['type'] ?? '';

        $items = [];
        foreach ((array) ($data['items'] ?? []) as $row) {
            if (!\is_array($row)) {
                continue;
            }
            $clean = [];
            foreach ($row as $key => $value) {
                $clean[(string) $key] = $value;
            }
            $items[] = $clean;
        }

        return new self(
            \is_string($component) ? $component : '',
            \is_string($type) ? $type : '',
            $items,
        );
    }

    public function getComponent(): string
    {
        return $this->component;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /** @return array<int, array<string, mixed>> */
    public function getItems(): array
    {
        return $this->items;
    }
}
