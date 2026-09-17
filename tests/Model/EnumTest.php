<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\Tests\Model;

use PHPUnit\Framework\TestCase;
use SarissaOps\Enviatodo\Enum\CarrierId;
use SarissaOps\Enviatodo\Enum\MexicanState;
use SarissaOps\Enviatodo\Enum\OrderStatus;
use SarissaOps\Enviatodo\Enum\PickupStatus;

final class EnumTest extends TestCase
{
    public function testOrderStatuses(): void
    {
        $this->assertCount(7, OrderStatus::cases());
        $this->assertSame(OrderStatus::Generated, OrderStatus::from(41));
        $this->assertSame('Guía generada', OrderStatus::Generated->label());
    }

    public function testOrderStatusFromLiveLabels(): void
    {
        $this->assertSame(OrderStatus::Generated, OrderStatus::tryFromLabel('GUÍA GENERADA'));
        $this->assertSame(OrderStatus::Generated, OrderStatus::tryFromLabel('guía generada'));
        $this->assertSame(OrderStatus::Returning, OrderStatus::tryFromLabel('Guia en devolución'));
        $this->assertSame(OrderStatus::Returning, OrderStatus::tryFromLabel('  guia EN devolucion  '));
        $this->assertNull(OrderStatus::tryFromLabel('No existe'));
        $this->assertNull(OrderStatus::tryFromLabel(''));
    }

    public function testPickupStatuses(): void
    {
        $this->assertCount(4, PickupStatus::cases());
        $this->assertSame(PickupStatus::Pending, PickupStatus::from(60));
    }

    public function testMexicanStates(): void
    {
        $this->assertCount(32, MexicanState::cases());
        $this->assertSame(MexicanState::NL, MexicanState::from('NL'));
        $this->assertSame('Nuevo León', MexicanState::NL->label());
    }

    public function testCarriers(): void
    {
        $this->assertCount(10, CarrierId::cases());
        $this->assertSame('ESTAFETA', CarrierId::Estafeta->name());
        $this->assertSame([11 => 'Aéreo', 12 => 'Terrestre'], CarrierId::Estafeta->services());
    }
}
