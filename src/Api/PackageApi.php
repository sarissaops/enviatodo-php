<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Api;

use Psr\Http\Message\ResponseInterface;
use SarissaOps\Enviatodo\Assert;
use SarissaOps\Enviatodo\Model\Package\DeleteResponse;
use SarissaOps\Enviatodo\Model\Package\Package;
use SarissaOps\Enviatodo\Model\Package\SavedPackage;

class PackageApi extends HttpApi
{
    /**
     * Required keys; the API 400-lists exactly these (`name` and
     * `id` are accepted but not required).
     */
    private const REQUIRED_KEYS = [
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
        'default_pkg',
    ];

    /**
     * Create (or update when 'id' is included) a package. The trailing slash
     * on add_package/ is significant to the API router — keep it.
     *
     * @param array<string, mixed> $data
     *
     * @return SavedPackage|array<mixed>|ResponseInterface
     */
    public function save(array $data)
    {
        foreach (self::REQUIRED_KEYS as $key) {
            Assert::keyExists($data, $key, sprintf('Package requires key "%s".', $key));
        }

        $response = $this->httpPost('Api/add_package/', $data);
        $result = $this->hydrateResponse($response, SavedPackage::class);
        assert($result instanceof SavedPackage || \is_array($result) || $result instanceof ResponseInterface);

        return $result;
    }

    /**
     * @return list<Package>|ResponseInterface
     */
    public function all()
    {
        return $this->hydrateList($this->httpGet('Api/get_packages'), Package::class);
    }

    /**
     * @return list<Package>|ResponseInterface
     */
    public function show(int $id)
    {
        return $this->hydrateList($this->httpGet(sprintf('Api/get_package_by_id/%d', $id)), Package::class);
    }

    /**
     * Deletes are GETs on this API.
     *
     * @return DeleteResponse|array<mixed>|ResponseInterface
     */
    public function delete(int $id)
    {
        $response = $this->httpGet(sprintf('Api/delete_package/%d', $id));
        $result = $this->hydrateResponse($response, DeleteResponse::class);
        assert($result instanceof DeleteResponse || \is_array($result) || $result instanceof ResponseInterface);

        return $result;
    }
}
