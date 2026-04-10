<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderCalculator;
use Tests\TestCase;

class OrderCalculatorTest extends TestCase
{
    public function test_kalem_net_applies_discount(): void
    {
        $order = new Order([
            'iskonto_yuzde' => 10,
            'kur_farki_yuzde' => 0,
            'kdv_orani' => 20,
            'is_manual_on' => false,
            'is_manual_nihai' => false,
        ]);
        $order->setRelation('items', collect([
            new OrderItem(['tutar' => 4000]),
        ]));

        $calc = new OrderCalculator;
        $p = $calc->calculate($order);

        $this->assertSame(4000.0, $p['kalem_brut_kdvsiz']);
        $this->assertSame(3600.0, $p['kalem_net_kdvsiz']);
        $this->assertFalse($p['on_is_manual']);
        $this->assertSame(3600.0, $p['hesaplanan_kdvsiz_on']);
    }

    public function test_manual_on_overrides_kalem_only_when_flagged(): void
    {
        $order = new Order([
            'iskonto_yuzde' => 10,
            'kur_farki_yuzde' => 0,
            'kdv_orani' => 20,
            'is_manual_on' => true,
            'tutar_kdvsiz_on' => 5000,
            'is_manual_nihai' => false,
        ]);
        $order->setRelation('items', collect([
            new OrderItem(['tutar' => 4000]),
        ]));

        $p = (new OrderCalculator)->calculate($order);

        $this->assertSame(3600.0, $p['kalem_net_kdvsiz']);
        $this->assertTrue($p['on_is_manual']);
        $this->assertSame(5000.0, $p['hesaplanan_kdvsiz_on']);
    }
}
