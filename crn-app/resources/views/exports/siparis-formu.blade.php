{{-- Panel sipariş infolist / kalem tablosu ile uyumlu - CRN Baca --}}
@php
    use App\Services\FormExportService;
    $kdvOran = (float) ($order->kdv_orani ?? 20);
    $kdvEtiket = abs($kdvOran - round($kdvOran)) < 0.001 ? (string) (int) round($kdvOran) : number_format($kdvOran, 2, '.', '');
    $yonTxt = FormExportService::orderYonEtiketi($order->yon);
    $ozellikTxt = FormExportService::orderOzelliklerMetni($order);
@endphp
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sipariş Formu - {{ $order->siparis_no }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 7px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 2px 3px; text-align: left; }
        th { background: #eee; }
        .header-table td { border: none; padding: 2px 6px 2px 0; }
        .label { font-weight: bold; width: 110px; }
        .text-right { text-align: right; }
        .toplam-row { font-weight: bold; }
    </style>
</head>
<body>
    <h2 style="text-align:center;">SİPARİŞ FORMU</h2>
    <table class="header-table">
        <tr>
            <td class="label">Tarih:</td>
            <td>{{ $order->siparis_tarihi?->format('d.m.Y') }}</td>
            <td></td>
            <td class="label">Sipariş No:</td>
            <td>{{ $order->siparis_no }}</td>
        </tr>
        <tr>
            <td class="label">Ön sipariş no:</td>
            <td colspan="4">{{ $order->on_siparis_no ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">SİPARİŞİ VEREN FİRMA</td>
            <td colspan="2">{{ $order->dealer?->unvan ?? '' }}</td>
            <td class="label">Firma Sıra No:</td>
            <td>{{ $order->dealer?->firma_no ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Ünvanı:</td>
            <td colspan="4">{{ $order->dealer?->unvan ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">İl/İlçe:</td>
            <td>{{ $order->dealer?->il_ilce ?? '' }}</td>
            <td class="label">Mail Adresi:</td>
            <td colspan="2">{{ $order->dealer?->mail ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Proje Adı:</td>
            <td colspan="2">{{ $order->proje_adi ?? '' }}</td>
            <td class="label">Cihaz Marka - Model:</td>
            <td>{{ $order->cihaz_marka_model ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">İlgili Kişi:</td>
            <td>{{ $order->dealer?->ilgili_kisi ?? '' }}</td>
            <td class="label">Tel:</td>
            <td>{{ $order->dealer?->tel ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Baca çapı (mm):</td>
            <td>{{ $order->bac_cap_mm !== null ? number_format((float) $order->bac_cap_mm, 2, ',', '.') : '—' }}</td>
            <td class="label">Baca yüksekliği (mm):</td>
            <td colspan="2">{{ $order->bac_yukseklik_mm !== null ? number_format((float) $order->bac_yukseklik_mm, 2, ',', '.') : '—' }}</td>
        </tr>
        <tr>
            <td class="label">Yön:</td>
            <td>{{ $yonTxt !== '' ? $yonTxt : '—' }}</td>
            <td class="label">Özellik kodları:</td>
            <td colspan="2">{{ $ozellikTxt !== '' ? $ozellikTxt : '—' }}</td>
        </tr>
    </table>
    <br>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>KOD</th>
                <th>AÇIKLAMA</th>
                <th>BİRİM</th>
                <th>BİRİM FİYAT</th>
                <th>ADET</th>
                <th>TUTAR</th>
                <th>KARŞI TEKLİF</th>
                <th>Uzunluk (m)</th>
                <th>Sac kalınlık</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $index => $item)
            @php $satirTutar = (float) ($item->tutar ?? ($item->birim_fiyat * $item->adet)); @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product?->malzeme_kodu ?? '' }}</td>
                <td>{{ $item->product?->malzeme_aciklamasi ?? '' }}</td>
                <td>{{ $item->product?->birim ?? 'AD' }}</td>
                <td class="text-right">{{ number_format((float) $item->birim_fiyat, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format((float) $item->adet, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($satirTutar, 2, ',', '.') }}</td>
                <td class="text-right">{{ $item->bayi_karsi_birim_fiyat !== null ? number_format((float) $item->bayi_karsi_birim_fiyat, 2, ',', '.') : '—' }}</td>
                <td class="text-right">{{ $item->product?->uzunluk_m !== null ? number_format((float) $item->product->uzunluk_m, 4, ',', '.') : '—' }}</td>
                <td class="text-right">{{ $item->product?->sac_kalinlik !== null ? number_format((float) $item->product->sac_kalinlik, 4, ',', '.') : '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <br>
    <table class="header-table">
        <tr>
            <td class="label">İskonto %:</td>
            <td>{{ $order->iskonto_yuzde !== null ? number_format((float) $order->iskonto_yuzde, 2, ',', '.') : '—' }}</td>
        </tr>
        <tr class="toplam-row">
            <td class="label">Kalem toplamı (iskonto sonrası, KDV hariç):</td>
            <td class="text-right">{{ number_format($order->kalem_net_kdvsiz, 2, ',', '.') }} TL</td>
        </tr>
        <tr>
            <td class="label">Ön tutar (hesaplanan, KDV hariç):</td>
            <td class="text-right">{{ number_format($order->hesaplanan_kdvsiz_on, 2, ',', '.') }} TL</td>
        </tr>
        <tr>
            <td class="label">Nihai taban (kur farkı sonrası):</td>
            <td class="text-right">{{ number_format($order->hesaplanan_kdvsiz_nihai, 2, ',', '.') }} TL</td>
        </tr>
        <tr class="toplam-row">
            <td class="label">Ara toplam (KDV hariç):</td>
            <td class="text-right">{{ number_format($order->ara_toplam_kdvsiz, 2, ',', '.') }} TL</td>
        </tr>
        <tr>
            <td class="label">KDV (%{{ $kdvEtiket }}):</td>
            <td class="text-right">{{ number_format($order->kdv_tutari, 2, ',', '.') }} TL</td>
        </tr>
        <tr class="toplam-row">
            <td class="label">GENEL TOPLAM:</td>
            <td class="text-right">{{ number_format($order->genel_toplam, 2, ',', '.') }} TL</td>
        </tr>
        @if($order->aciklama)
        <tr>
            <td class="label">Açıklama:</td>
            <td>{{ $order->aciklama }}</td>
        </tr>
        @endif
    </table>
</body>
</html>
