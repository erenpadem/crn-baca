<?php

namespace App\Services;

use App\Models\Order;

/**
 * Sipariş KDV hariç / KDV dahil tutarlarının tek hesaplama noktası.
 *
 * Kurallar:
 * - Kalem tabanı: satır tutarları toplamı; iskonto varsa buna uygulanır (kalem_net_kdvsiz).
 * - Ön tutar: is_manual_on=true ve tutar_kdvsiz_on doluysa manuel; aksi halde kalem_net_kdvsiz.
 * - Nihai taban: is_manual_nihai=true ve tutar_kdvsiz_nihai doluysa manuel; aksi halde ön × (1 + kur_farki_yuzde/100).
 * - kur (kur değeri): referans bilgisidir; TRY tutarlarını çarpmaz veya bölmez (yanıltıcı “sessiz” etki yok).
 */
final class OrderCalculator
{
    /**
     * @return array{
     *     kalem_brut_kdvsiz: float,
     *     kalem_net_kdvsiz: float,
     *     on_is_manual: bool,
     *     hesaplanan_kdvsiz_on: float,
     *     nihai_is_manual: bool,
     *     hesaplanan_kdvsiz_nihai: float,
     *     opsiyonel_toplam_kdvsiz: float,
     *     ara_toplam_kdvsiz: float,
     *     kdv_tutari: float,
     *     genel_toplam: float,
     *     kur_reference: float|null,
     *     kur_farki_yuzde: float,
     * }
     */
    public function calculate(Order $order): array
    {
        $order->loadMissing('items');

        $kalemBrut = round((float) $order->items->sum('tutar'), 4);
        $iskonto = $order->iskonto_yuzde !== null && (float) $order->iskonto_yuzde > 0
            ? (float) $order->iskonto_yuzde
            : 0.0;
        $kalemNet = $iskonto > 0
            ? round($kalemBrut * (1 - $iskonto / 100), 4)
            : $kalemBrut;

        $onIsManual = (bool) $order->is_manual_on;
        $manualOn = $order->tutar_kdvsiz_on !== null ? (float) $order->tutar_kdvsiz_on : null;

        if ($onIsManual && $manualOn !== null) {
            $hesaplananOn = round($manualOn, 4);
        } else {
            $hesaplananOn = $kalemNet;
        }

        $nihaiIsManual = (bool) $order->is_manual_nihai;
        $manualNihai = $order->tutar_kdvsiz_nihai !== null ? (float) $order->tutar_kdvsiz_nihai : null;

        if ($nihaiIsManual && $manualNihai !== null) {
            $hesaplananNihai = round($manualNihai, 4);
        } else {
            $kurFark = (float) ($order->kur_farki_yuzde ?? 0);
            $hesaplananNihai = round($hesaplananOn * (1 + $kurFark / 100), 4);
        }

        $opsiyonel = $this->opsiyonelToplamKdvsiz($order);
        $ara = round($hesaplananNihai + $opsiyonel, 4);
        $kdvOran = (float) ($order->kdv_orani ?? 20);
        $kdv = round($ara * ($kdvOran / 100), 4);
        $genel = round($ara + $kdv, 4);

        return [
            'kalem_brut_kdvsiz' => $kalemBrut,
            'kalem_net_kdvsiz' => $kalemNet,
            'on_is_manual' => $onIsManual,
            'hesaplanan_kdvsiz_on' => $hesaplananOn,
            'nihai_is_manual' => $nihaiIsManual,
            'hesaplanan_kdvsiz_nihai' => $hesaplananNihai,
            'opsiyonel_toplam_kdvsiz' => $opsiyonel,
            'ara_toplam_kdvsiz' => $ara,
            'kdv_tutari' => $kdv,
            'genel_toplam' => $genel,
            'kur_reference' => $order->kur !== null ? (float) $order->kur : null,
            'kur_farki_yuzde' => (float) ($order->kur_farki_yuzde ?? 0),
        ];
    }

    private function opsiyonelToplamKdvsiz(Order $order): float
    {
        $t = 0.0;
        if ($order->opsiyonel_nakliye && $order->nakliye_tutari) {
            $t += (float) $order->nakliye_tutari;
        }
        if ($order->opsiyonel_akreditif && $order->akreditif_tutari) {
            $t += (float) $order->akreditif_tutari;
        }
        if ($order->opsiyonel_montaj && $order->montaj_tutari) {
            $t += (float) $order->montaj_tutari;
        }
        if ($order->opsiyonel_havalandirma && $order->havalandirma_tutari) {
            $t += (float) $order->havalandirma_tutari;
        }
        if ($order->opsiyonel_diger && $order->diger_tutari) {
            $t += (float) $order->diger_tutari;
        }

        return round($t, 4);
    }
}
