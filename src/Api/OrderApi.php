<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Api;

use Psr\Http\Message\ResponseInterface;
use SarissaOps\Enviatodo\Assert;
use SarissaOps\Enviatodo\Model\Order\CancelResponse;
use SarissaOps\Enviatodo\Model\Order\Order;
use SarissaOps\Enviatodo\Model\Order\OrderCreated;
use SarissaOps\Enviatodo\Model\Order\TransactionUser;

class OrderApi extends HttpApi
{
    /**
     * Create an order from a quote uuid. Mutating: mints a real label.
     *
     * @return OrderCreated|array<mixed>|ResponseInterface
     */
    public function create(string $uuid, string $providerId, string $serviceId, bool $insurance)
    {
        Assert::stringNotEmpty($uuid, 'Order creation requires a quote uuid.');

        $response = $this->httpPost('Api/create_order', [
            'order' => [
                'type' => 'create_order',
                'data' => [
                    'uuid' => $uuid,
                    'detail' => [
                        'provider_id' => $providerId,
                        'provider_service_id' => $serviceId,
                        'insurance' => $insurance,
                    ],
                ],
            ],
        ]);
        $result = $this->hydrateResponse($response, OrderCreated::class);
        assert($result instanceof OrderCreated || \is_array($result) || $result instanceof ResponseInterface);

        return $result;
    }

    /**
     * Cancel an order. The refund lands ~7 days later — not synchronous.
     *
     * @param list<string> $trackingIds
     *
     * @return CancelResponse|array<mixed>|ResponseInterface
     */
    public function cancel(
        array $trackingIds,
        string $cancelledAt,
        string $refundedAt,
        string $reasonValue,
        string $reasonText,
        string $orderType,
    ) {
        Assert::isList($trackingIds);
        Assert::notEmpty($trackingIds, 'Cancel requires at least one tracking id.');

        $response = $this->httpPost('Api/cancel_order', [
            'tracking_ids' => $trackingIds,
            'date' => ['cancelled_at' => $cancelledAt, 'refunded_at' => $refundedAt],
            'reason' => ['value' => $reasonValue, 'text' => $reasonText],
            'order_type' => $orderType,
        ]);
        $result = $this->hydrateResponse($response, CancelResponse::class);
        assert($result instanceof CancelResponse || \is_array($result) || $result instanceof ResponseInterface);

        return $result;
    }

    /**
     * @return list<Order>|ResponseInterface
     */
    public function all()
    {
        return $this->hydrateList($this->httpGet('Api/get_orders'), Order::class);
    }

    /**
     * Single fetch (Swagger-only operation).
     *
     * @return list<Order>|ResponseInterface
     */
    public function show(string $trxId)
    {
        Assert::stringNotEmpty($trxId);

        return $this->hydrateList($this->httpGet(sprintf('Api/get_order/%s', $trxId)), Order::class);
    }

    /**
     * Filtered history. `recipient` is always sent (required by the API
     * contract, absent from the collection samples).
     *
     * @param array<string, mixed> $criteria
     *
     * @return list<Order>|ResponseInterface
     */
    public function filter(array $criteria)
    {
        $criteria['recipient'] ??= null;

        return $this->hydrateList($this->httpPost('Api/get_orders_filter', $criteria), Order::class);
    }

    /**
     * @return list<TransactionUser>|ResponseInterface
     */
    public function transactions()
    {
        return $this->hydrateList($this->httpGet('Api/user_transactions'), TransactionUser::class);
    }
}
