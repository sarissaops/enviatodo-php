<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Enum;

/**
 * Pickup (recolección) statuses from Enviatodo_status.json, type PICKUP.
 */
enum PickupStatus: int
{
    case Pending = 60;
    case Generated = 61;
    case Cancelled = 62;
    case Rescheduled = 71;

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Recolección pendiente',
            self::Generated => 'Recolección generada',
            self::Cancelled => 'Recolección cancelada',
            self::Rescheduled => 'Recolección Reprogramada',
        };
    }
}
