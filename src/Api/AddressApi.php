<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Api;

use Psr\Http\Message\ResponseInterface;
use SarissaOps\Enviatodo\Assert;
use SarissaOps\Enviatodo\Model\Address\Address;
use SarissaOps\Enviatodo\Model\Address\DeleteResponse;
use SarissaOps\Enviatodo\Model\Address\SavedAddress;

class AddressApi extends HttpApi
{
    /**
     * Required keys; the API answers 400
     * listing exactly these when absent (lat/lng/int_number/reference/
     * default_addr are optional).
     */
    private const REQUIRED_KEYS = [
        'address_type_id',
        'full_name',
        'email',
        'telephone',
        'street',
        'ext_number',
        'zip_code',
        'suburb',
        'municipality',
        'town',
        'state',
        'state_code',
        'country_code',
    ];

    /**
     * Create (or update when 'id' is included) an address.
     *
     * @param array<string, mixed> $data
     *
     * @return SavedAddress|array<mixed>|ResponseInterface
     */
    public function save(array $data)
    {
        foreach (self::REQUIRED_KEYS as $key) {
            Assert::keyExists($data, $key, sprintf('Address requires key "%s".', $key));
        }

        $response = $this->httpPost('Api/add_address', $data);
        $result = $this->hydrateResponse($response, SavedAddress::class);
        assert($result instanceof SavedAddress || \is_array($result) || $result instanceof ResponseInterface);

        return $result;
    }

    /**
     * @return list<Address>|ResponseInterface
     */
    public function all()
    {
        return $this->hydrateList($this->httpGet('Api/get_address'), Address::class);
    }

    /**
     * Single fetch still returns a list envelope.
     *
     * @return list<Address>|ResponseInterface
     */
    public function show(int $id)
    {
        return $this->hydrateList($this->httpGet(sprintf('Api/get_address_by_id/%d', $id)), Address::class);
    }

    /**
     * @return list<Address>|ResponseInterface
     */
    public function byType(int $type)
    {
        return $this->hydrateList(
            $this->httpGet(sprintf('Api/get_address_by_type_id/%d', $type), [], ['x-enviatodo-client' => '1']),
            Address::class,
        );
    }

    /**
     * Deletes are GETs on this API.
     *
     * @return DeleteResponse|array<mixed>|ResponseInterface
     */
    public function delete(int $id)
    {
        $response = $this->httpGet(sprintf('Api/delete_address_by_id/%d', $id));
        $result = $this->hydrateResponse($response, DeleteResponse::class);
        assert($result instanceof DeleteResponse || \is_array($result) || $result instanceof ResponseInterface);

        return $result;
    }
}
