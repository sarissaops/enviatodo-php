<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Enum;

/**
 * Mexican state codes from Codigos de estado.txt (Enviatodo-specific, non-ISO).
 */
enum MexicanState: string
{
    case AS = 'AS';
    case BC = 'BC';
    case BS = 'BS';
    case CC = 'CC';
    case CL = 'CL';
    case CM = 'CM';
    case CS = 'CS';
    case CH = 'CH';
    case DF = 'DF';
    case DG = 'DG';
    case GT = 'GT';
    case GR = 'GR';
    case HG = 'HG';
    case JC = 'JC';
    case MC = 'MC';
    case MN = 'MN';
    case MS = 'MS';
    case NT = 'NT';
    case NL = 'NL';
    case OC = 'OC';
    case PL = 'PL';
    case QO = 'QO';
    case QR = 'QR';
    case SP = 'SP';
    case SL = 'SL';
    case SR = 'SR';
    case TC = 'TC';
    case TS = 'TS';
    case TL = 'TL';
    case VZ = 'VZ';
    case YN = 'YN';
    case ZS = 'ZS';

    public function label(): string
    {
        return match ($this) {
            self::AS => 'Aguascalientes',
            self::BC => 'Baja California',
            self::BS => 'Baja California Sur',
            self::CC => 'Campeche',
            self::CL => 'Coahuila',
            self::CM => 'Colima',
            self::CS => 'Chiapas',
            self::CH => 'Chihuahua',
            self::DF => 'Ciudad de México',
            self::DG => 'Durango',
            self::GT => 'Guanajuato',
            self::GR => 'Guerrero',
            self::HG => 'Hidalgo',
            self::JC => 'Jalisco',
            self::MC => 'México',
            self::MN => 'Michoacán',
            self::MS => 'Morelos',
            self::NT => 'Nayarit',
            self::NL => 'Nuevo León',
            self::OC => 'Oaxaca',
            self::PL => 'Puebla',
            self::QO => 'Querétaro',
            self::QR => 'Quintana Roo',
            self::SP => 'San Luis Potosí',
            self::SL => 'Sinaloa',
            self::SR => 'Sonora',
            self::TC => 'Tabasco',
            self::TS => 'Tamaulipas',
            self::TL => 'Tlaxcala',
            self::VZ => 'Veracruz',
            self::YN => 'Yucatán',
            self::ZS => 'Zacatecas',
        };
    }
}
