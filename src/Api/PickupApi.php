<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Api;

use Psr\Http\Message\ResponseInterface;
use SarissaOps\Enviatodo\Assert;
use SarissaOps\Enviatodo\Exception\InvalidArgumentException;
use SarissaOps\Enviatodo\Model\Pickup\PickupOrder;
use SarissaOps\Enviatodo\Model\Pickup\PickupResponse;

class PickupApi extends HttpApi
{
    /**
     * Required keys; contact_email is required
     * despite being absent from the collection samples.
     */
    private const CREATE_KEYS = [
        'who_delivers',
        'contact_phone',
        'contact_email',
        'data',
        'pickup_date',
        'pickup_place',
        'pickup_place_data',
        'origin',
        'provider_id',
        'description_reference',
    ];

    /**
     * @param list<string> $providers
     *
     * @return list<PickupOrder>|ResponseInterface
     */
    public function ordersForPickup(array $providers)
    {
        Assert::isList($providers);
        Assert::notEmpty($providers, 'Pickup lookup requires at least one provider id.');

        return $this->hydrateList(
            $this->httpPost('Api/get_orders_for_pickup_by_client_id', ['providers' => $providers]),
            PickupOrder::class,
        );
    }

    /**
     * All guides in one pickup must share the same origin address — validated
     * client-side before any HTTP traffic.
     *
     * @param array<string, mixed> $data
     *
     * @return PickupResponse|array<mixed>|ResponseInterface
     */
    public function create(array $data)
    {
        foreach (self::CREATE_KEYS as $key) {
            Assert::keyExists($data, $key, sprintf('Pickup requires key "%s".', $key));
        }

        $rows = $data['data'];
        if (!\is_array($rows) || [] === $rows) {
            throw new InvalidArgumentException('Pickup requires at least one order row.');
        }

        $origins = [];
        foreach ($rows as $row) {
            if (!\is_array($row)) {
                throw new InvalidArgumentException('Pickup order rows must be arrays.');
            }
            $origin = $row['origin_address'] ?? null;
            if (!\is_string($origin) || '' === $origin) {
                throw new InvalidArgumentException('Pickup order rows require an origin_address.');
            }
            $origins[$origin] = true;
        }
        if (\count($origins) > 1) {
            throw new InvalidArgumentException('All guides in one pickup must share the same origin address.');
        }

        $response = $this->httpPost('Api/add_pickup', $data);
        $result = $this->hydrateResponse($response, PickupResponse::class);
        assert($result instanceof PickupResponse || \is_array($result) || $result instanceof ResponseInterface);

        return $result;
    }

    /**
     * @return PickupResponse|array<mixed>|ResponseInterface
     */
    public function cancel(int $id, string $description, string $userId)
    {
        $response = $this->httpPost('Api/cancel_pickup', [
            'id_recollection' => $id,
            'cancel_description' => $description,
            'user_id' => $userId,
        ]);
        $result = $this->hydrateResponse($response, PickupResponse::class);
        assert($result instanceof PickupResponse || \is_array($result) || $result instanceof ResponseInterface);

        return $result;
    }
}
