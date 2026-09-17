<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Model\Address;

use SarissaOps\Enviatodo\Model\ApiResponse;

final class Address implements ApiResponse
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

        return new self($fields);
    }

    public function getId(): string
    {
        return $this->fields['id'];
    }

    public function getAddressTypeId(): string
    {
        return $this->fields['address_type_id'];
    }

    public function getFullName(): string
    {
        return $this->fields['full_name'];
    }

    public function getEmail(): string
    {
        return $this->fields['email'];
    }

    public function getTelephone(): string
    {
        return $this->fields['telephone'];
    }

    public function getStreet(): string
    {
        return $this->fields['street'];
    }

    public function getExtNumber(): string
    {
        return $this->fields['ext_number'];
    }

    public function getIntNumber(): string
    {
        return $this->fields['int_number'];
    }

    public function getZipCode(): string
    {
        return $this->fields['zip_code'];
    }

    public function getSuburb(): string
    {
        return $this->fields['suburb'];
    }

    public function getMunicipality(): string
    {
        return $this->fields['municipality'];
    }

    public function getTown(): string
    {
        return $this->fields['town'];
    }

    public function getState(): string
    {
        return $this->fields['state'];
    }

    public function getStateCode(): string
    {
        return $this->fields['state_code'];
    }

    public function getCountryCode(): string
    {
        return $this->fields['country_code'];
    }

    public function getReference(): string
    {
        return $this->fields['reference'];
    }

    public function getCompany(): string
    {
        return $this->fields['company'];
    }

    public function getDefaultAddr(): string
    {
        return $this->fields['default_addr'];
    }

    public function getLat(): string
    {
        return $this->fields['lat'];
    }

    public function getLng(): string
    {
        return $this->fields['lng'];
    }

    public function getCreatedAt(): string
    {
        return $this->fields['created_at'];
    }

    public function getUpdatedAt(): string
    {
        return $this->fields['updated_at'];
    }

    public function getStatusId(): string
    {
        return $this->fields['status_id'];
    }

    public function getName(): string
    {
        return $this->fields['name'];
    }

    private const KEYS = [
        'id',
        'address_type_id',
        'full_name',
        'email',
        'telephone',
        'street',
        'ext_number',
        'int_number',
        'zip_code',
        'suburb',
        'municipality',
        'town',
        'state',
        'state_code',
        'country_code',
        'reference',
        'company',
        'default_addr',
        'lat',
        'lng',
        'created_at',
        'updated_at',
        'status_id',
        'name',
    ];

    private static function string(mixed $value): string
    {
        if (\is_bool($value)) {
            return $value ? '1' : '0';
        }

        return \is_string($value) || \is_int($value) || \is_float($value) ? (string) $value : '';
    }
}
