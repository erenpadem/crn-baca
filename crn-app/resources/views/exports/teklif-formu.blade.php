{{-- Panel teklif infolist / kalem tablosu ile uyumlu - CRN Baca --}}
@php
    use App\Services\FormExportService;
    $netKdvsiz = FormExportService::quoteNetKdvsizForKdv($quote);
    $kdvYuzde = FormExportService::quoteKdvOraniYuzde();
    $kdvTutari = round($netKdvsiz * ($kdvYuzde / 100), 4);
    $genelToplam = round($netKdvsiz + $kdvTutari, 4);
    $kalemToplam = (float) $quote->items->sum(fn ($i) => (float) ($i->tutar ?? ($i->birim_fiyat * $i->adet)));
@endphp
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Teklif Formu - {{ $quote->teklif_no }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 3px 4px; text-align: left; }
        th { background: #eee; }
        .header-table td { border: none; padding: 2px 6px 2px 0; }
        .label { font-weight: bold; width: 120px; }
        .text-right { text-align: right; }
        .toplam-row { font-weight: bold; }
    </style>
</head>
<body>
    <h2 style="text-align:center;">SİPARİŞ / TEKLİF FORMU</h2>
    <table class="header-table">
        <tr>
            <td class="label">Tarih:</td>
            <td>{{ $quote->created_at?->format('d.m.Y') }}</td>
            <td></td>
            <td class="label">Teklif No:</td>
            <td>{{ $quote->teklif_no }}</td>
        </tr>
        <tr>
            <td class="label">SİPARİŞİ VEREN FİRMA</td>
            <td colspan="2">{{ $quote->dealer?->unvan ?? '' }}</td>
            <td class="label">Firma Sıra No:</td>
            <td>{{ $quote->dealer?->firma_no ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Ünvanı:</td>
            <td colspan="4">{{ $quote->dealer?->unvan ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">İl/İlçe:</td>
            <td>{{ $quote->dealer?->il_ilce ?? '' }}</td>
            <td class="label">Mail Adresi:</td>
            <td colspan="2">{{ $quote->dealer?->mail ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Proje Adı:</td>
            <td colspan="2">{{ $quote->proje_adi ?? '' }}</td>
            <td class="label">Cihaz Marka - Model:</td>
            <td>{{ $quote->cihaz_marka_model ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">İlgili Kişi:</td>
            <td>{{ $quote->dealer?->ilgili_kisi ?? '' }}</td>
            <td class="label">Tel:</td>
            <td>{{ $quote->dealer?->tel ?? '' }}</td>
        </tr>
    </table>
    <br>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>MALZEME KODU</th>
                <th>MALZEME AÇIKLAMASI</th>
                <th>BİRİM</th>
                <th>BİRİM FİYAT (İÇ)</th>
                <th>ADET</th>
                <th>MÜŞ. MALİYET</th>
                <th>MÜŞ. SATIŞ</th>
                <th>TUTAR</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quote->items as $index => $item)
            @php $satirTutar = (float) ($item->tutar ?? ($item->birim_fiyat * $item->adet)); @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product?->malzeme_kodu ?? '' }}</td>
                <td>{{ $item->product?->malzeme_aciklamasi ?? '' }}</td>
                <td>{{ $item->product?->birim ?? 'AD' }}</td>
                <td class="text-right">{{ number_format((float) $item->birim_fiyat, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format((float) $item->adet, 2, ',', '.') }}</td>
                <td class="text-right">{{ $item->musteri_maliyet_birim !== null ? number_format((float) $item->musteri_maliyet_birim, 2, ',', '.') : '—' }}</td>
                <td class="text-right">{{ $item->musteri_birim_fiyat !== null ? number_format((float) $item->musteri_birim_fiyat, 2, ',', '.') : '—' }}</td>
                <td class="text-right">{{ number_format($satirTutar, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <br>
    <table class="header-table">
        <tr>
            <td class="label">İskonto %:</td>
            <td>{{ $quote->musteri_iskonto_yuzde !== null ? number_format((float) $quote->musteri_iskonto_yuzde, 2, ',', '.') : '—' }}</td>
        </tr>
        <tr>
            <td class="label">Müşteri net (KDV hariç, manuel):</td>
            <td>{{ $quote->musteri_net_tutar !== null ? number_format((float) $quote->musteri_net_tutar, 2, ',', '.') . ' TL' : '—' }}</td>
        </tr>
        <tr class="toplam-row">
            <td class="label">Kalem toplamı (satır tutarları):</td>
            <td class="text-right">{{ number_format($kalemToplam, 2, ',', '.') }} TL</td>
        </tr>
        <tr class="toplam-row">
            <td class="label">Net tutar (KDV matrahı):</td>
            <td class="text-right">{{ number_format($netKdvsiz, 2, ',', '.') }} TL</td>
        </tr>
        <tr>
            <td class="label">KDV (%{{ (int) round($kdvYuzde) }}):</td>
            <td class="text-right">{{ number_format($kdvTutari, 2, ',', '.') }} TL</td>
        </tr>
        <tr class="toplam-row">
            <td class="label">TOPLAM:</td>
            <td class="text-right">{{ number_format($genelToplam, 2, ',', '.') }} TL</td>
        </tr>
        @if($quote->musteri_not)
        <tr>
            <td class="label">Müşteri notu:</td>
            <td>{{ $quote->musteri_not }}</td>
        </tr>
        @endif
    </table>
</body>
</html>
