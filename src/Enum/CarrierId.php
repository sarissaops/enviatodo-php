<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Enum;

/**
 * Carrier (provider) ids + their services from the carrier table.
 */
enum CarrierId: int
{
    case Fedex = 1;
    case Paquetexpress = 4;
    case Dhl = 5;
    case Sendex = 8;
    case Estafeta = 9;
    case Bigsmart = 11;
    case GrupoAmpm = 12;
    case Afimex = 14;
    case Imile = 15;
    case Uber = 16;

    public function name(): string
    {
        return match ($this) {
            self::Fedex => 'FEDEX',
            self::Paquetexpress => 'PAQUETEXPRESS',
            self::Dhl => 'DHL',
            self::Sendex => 'SENDEX',
            self::Estafeta => 'ESTAFETA',
            self::Bigsmart => 'BIGSMART',
            self::GrupoAmpm => 'GRUPO AMPM',
            self::Afimex => 'AFIMEX',
            self::Imile => 'IMILE',
            self::Uber => 'UBER',
        };
    }

    /**
     * @return array<int, string> provider_service_id => transport mode.
     */
    public function services(): array
    {
        return match ($this) {
            self::Fedex => [1 => 'Terrestre', 2 => 'Aéreo'],
            self::Paquetexpress => [5 => 'Terrestre'],
            self::Dhl => [6 => 'Aéreo', 16 => 'Terrestre'],
            self::Sendex => [9 => 'Terrestre'],
            self::Estafeta => [11 => 'Aéreo', 12 => 'Terrestre'],
            self::Bigsmart => [18 => 'Local Terrestre', 19 => 'Foraneo 1', 20 => 'Foraneo 2'],
            self::GrupoAmpm => [21 => 'Estandar Terrestre'],
            self::Afimex => [23 => 'Estandar Terrestre'],
            self::Imile => [24 => 'Estandar Terrestre'],
            self::Uber => [25 => 'Envío Express 2 ruedas', 26 => 'Envío Express 4 ruedas'],
        };
    }
}
