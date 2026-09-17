<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Model\Order;

use SarissaOps\Enviatodo\Model\ApiResponse;

final class OrderCreated implements ApiResponse
{
    private Summary $summary;

    /** @var list<Guide> */
    private array $guides;

    /**
     * @param list<Guide> $guides
     */
    private function __construct(Summary $summary, array $guides)
    {
        $this->summary = $summary;
        $this->guides = $guides;
    }

    /** @param array<array-key, mixed> $data */
    public static function create(array $data): static
    {
        $summary = $data['summary'] ?? [];
        if (!\is_array($summary)) {
            $summary = [];
        }
        /** @var array<array-key, mixed> $summaryPayload */
        $summaryPayload = [];
        foreach ($summary as $key => $value) {
            $summaryPayload[$key] = $value;
        }

        $guides = [];
        foreach ((array) ($data['guides'] ?? []) as $row) {
            if (!\is_array($row)) {
                continue;
            }
            /** @var array<array-key, mixed> $payload */
            $payload = [];
            foreach ($row as $key => $value) {
                $payload[$key] = $value;
            }
            $guides[] = Guide::create($payload);
        }

        return new self(Summary::create($summaryPayload), $guides);
    }

    public function getSummary(): Summary
    {
        return $this->summary;
    }

    /** @return list<Guide> */
    public function getGuides(): array
    {
        return $this->guides;
    }
}
