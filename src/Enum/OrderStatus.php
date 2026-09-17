<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Enum;

/**
 * Order (guía) statuses from Enviatodo_status.json, type ORDER.
 */
enum OrderStatus: int
{
    case Generated = 41;
    case InTransit = 42;
    case Cancelled = 43;
    case Delivered = 44;
    case InPickup = 59;
    case Returning = 68;
    case Incident = 69;

    public function label(): string
    {
        return match ($this) {
            self::Generated => 'Guía generada',
            self::InTransit => 'Guía en transito',
            self::Cancelled => 'Guía cancelada',
            self::Delivered => 'Guía entregada',
            self::InPickup => 'Recolección',
            self::Returning => 'Guia en devolución',
            self::Incident => 'Incidencia en guía',
        };
    }

    /**
     * Match a live status label (e.g. "GUÍA GENERADA") regardless of case,
     * accents, or surrounding whitespace.
     */
    public static function tryFromLabel(string $label): ?self
    {
        $normalized = self::normalize($label);
        if ('' === $normalized) {
            return null;
        }
        foreach (self::cases() as $case) {
            if (self::normalize($case->label()) === $normalized) {
                return $case;
            }
        }

        return null;
    }

    private static function normalize(string $value): string
    {
        $value = trim($value);
        if ('' === $value) {
            return '';
        }
        $folded = strtr($value, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
            'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ü' => 'u', 'Ñ' => 'n',
        ]);

        return strtolower(preg_replace('/\s+/', ' ', $folded) ?? '');
    }
}
