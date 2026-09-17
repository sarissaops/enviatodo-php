<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Model\Package;

use SarissaOps\Enviatodo\Model\ApiResponse;

final class Package implements ApiResponse
{
    /** @var array<string, string> */
    private array $fields;

    /** @param array<string, string> $fields */
    private function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    /** @param array<array-key, mixed> $data */
    public static function create(array $data): static
    {
        $fields = [];
        foreach (self::KEYS as $key) {
            $fields[$key] = self::string($data[$key] ?? '');
        }
        // List rows carry `default`, single shapes carry `default_pkg`.
        $fields['default_pkg'] = self::string($data['default_pkg'] ?? $data['default'] ?? '');

        return new self($fields);
    }

    public function getId(): string
    {
        return $this->fields['id'];
    }

    public function getName(): string
    {
        return $this->fields['name'];
    }

    public function getProductType(): string
    {
        return $this->fields['product_type'];
    }

    public function getUnitType(): string
    {
        return $this->fields['unit_type'];
    }

    public function getPackageContent(): string
    {
        return $this->fields['package_content'];
    }

    public function getAmountPkg(): string
    {
        return $this->fields['amount_pkg'];
    }

    public function getHeight(): string
    {
        return $this->fields['height'];
    }

    public function getWidth(): string
    {
        return $this->fields['width'];
    }

    public function getLength(): string
    {
        return $this->fields['length'];
    }

    public function getWeight(): string
    {
        return $this->fields['weight'];
    }

    public function getRealWeight(): string
    {
        return $this->fields['real_weight'];
    }

    public function getVolumetricWeight(): string
    {
        return $this->fields['volumetric_weight'];
    }

    public function getBillWeight(): string
    {
        return $this->fields['bill_weight'];
    }

    public function getDefaultPkg(): string
    {
        return $this->fields['default_pkg'];
    }

    public function getUnitWeight(): string
    {
        return $this->fields['unit_weight'];
    }

    public function getUnitLength(): string
    {
        return $this->fields['unit_length'];
    }

    public function getCreatedAt(): string
    {
        return $this->fields['created_at'];
    }

    public function getUpdatedAt(): string
    {
        return $this->fields['updated_at'];
    }

    private const KEYS = [
        'id',
        'name',
        'product_type',
        'unit_type',
        'package_content',
        'amount_pkg',
        'height',
        'width',
        'length',
        'weight',
        'real_weight',
        'volumetric_weight',
        'bill_weight',
        'unit_weight',
        'unit_length',
        'created_at',
        'updated_at',
    ];

    private static function string(mixed $value): string
    {
        if (\is_bool($value)) {
            return $value ? '1' : '0';
        }

        return \is_string($value) || \is_int($value) || \is_float($value) ? (string) $value : '';
    }
}
