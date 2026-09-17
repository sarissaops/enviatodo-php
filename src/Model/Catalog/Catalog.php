<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Model\Catalog;

use SarissaOps\Enviatodo\Model\ApiResponse;

final class Catalog implements ApiResponse
{
    private string $component;

    private string $type;

    /** @var array<int, array{key: string, value: string}> */
    private array $items;

    /**
     * @param array<int, array{key: string, value: string}> $items
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
            $key = $row['key'] ?? '';
            $value = $row['value'] ?? '';
            $items[] = [
                'key' => \is_string($key) || \is_int($key) ? (string) $key : '',
                'value' => \is_string($value) || \is_int($value) ? (string) $value : '',
            ];
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

    /** @return array<int, array{key: string, value: string}> */
    public function getItems(): array
    {
        return $this->items;
    }
}
