<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FormExportService
{
    /** Teklif formunu PDF olarak indir (Excel Sipariş Formu yapısına sadık) */
    public static function quotePdf(Quote $quote): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = Pdf::loadView('exports.teklif-formu', ['quote' => $quote->load(['dealer', 'items.product'])]);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('teklif-'.$quote->teklif_no.'.pdf', ['Attachment' => true]);
    }

    /** Sipariş formunu PDF olarak indir */
    public static function orderPdf(Order $order): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = Pdf::loadView('exports.siparis-formu', ['order' => $order->load(['dealer', 'items.product'])]);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('siparis-'.$order->siparis_no.'.pdf', ['Attachment' => true]);
    }

    /** Teklif formunu Excel olarak indir – okunaklı, bölümlü yapı */
    public static function quoteExcel(Quote $quote): StreamedResponse
    {
        $quote->load(['dealer', 'items.product']);
        $kalemToplam = (float) $quote->items->sum(fn ($i) => (float) ($i->tutar ?? ($i->birim_fiyat * $i->adet)));
        $netKdvsiz = self::quoteNetKdvsizForKdv($quote);
        $kdvYuzde = self::quoteKdvOraniYuzde();
        $kdvTutari = round($netKdvsiz * ($kdvYuzde / 100), 4);
        $genelToplam = round($netKdvsiz + $kdvTutari, 4);
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Teklif');

        $row = 1;
        $sheet->mergeCells('A'.$row.':B'.$row);
        $sheet->setCellValue('A'.$row, 'SİPARİŞ / TEKLİF FORMU');
        $sheet->getStyle('A'.$row)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row += 2;

        $sheet->setCellValue('A'.$row, 'GENEL BİLGİLER');
        $sheet->mergeCells('A'.$row.':B'.$row);
        $sheet->getStyle('A'.$row)->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E3F2FD');
        $row++;
        $sheet->setCellValue('A'.$row, 'Tarih');
        $sheet->setCellValue('B'.$row, $quote->created_at?->format('d.m.Y') ?? '');
        $row++;
        $sheet->setCellValue('A'.$row, 'Teklif No');
        $sheet->setCellValue('B'.$row, $quote->teklif_no);
        $row += 2;

        $sheet->setCellValue('A'.$row, 'FİRMA BİLGİLERİ');
        $sheet->mergeCells('A'.$row.':B'.$row);
        $sheet->getStyle('A'.$row)->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E3F2FD');
        $row++;
        $sheet->setCellValue('A'.$row, 'Firma Ünvanı');
        $sheet->setCellValue('B'.$row, $quote->dealer?->unvan ?? '');
        $row++;
        $sheet->setCellValue('A'.$row, 'Firma No');
        $sheet->setCellValue('B'.$row, $quote->dealer?->firma_no ?? '');
        $row++;
        $sheet->setCellValue('A'.$row, 'İl/İlçe');
        $sheet->setCellValue('B'.$row, $quote->dealer?->il_ilce ?? '');
        $row++;
        $sheet->setCellValue('A'.$row, 'Mail');
        $sheet->setCellValue('B'.$row, $quote->dealer?->mail ?? '');
        $row++;
        $sheet->setCellValue('A'.$row, 'İlgili Kişi');
        $sheet->setCellValue('B'.$row, $quote->dealer?->ilgili_kisi ?? '');
        $row++;
        $sheet->setCellValue('A'.$row, 'Tel');
        $sheet->setCellValue('B'.$row, $quote->dealer?->tel ?? '');
        $row++;
        $sheet->setCellValue('A'.$row, 'Proje Adı');
        $sheet->setCellValue('B'.$row, $quote->proje_adi ?? '');
        $row++;
        $sheet->setCellValue('A'.$row, 'Cihaz Marka - Model');
        $sheet->setCellValue('B'.$row, $quote->cihaz_marka_model ?? '');
        $row += 2;

        $sheet->setCellValue('A'.$row, 'KALEMLER');
        $sheet->mergeCells('A'.$row.':I'.$row);
        $sheet->getStyle('A'.$row)->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E3F2FD');
        $row++;
        $headerRow = $row;
        $sheet->setCellValue('A'.$row, 'No');
        $sheet->setCellValue('B'.$row, 'Malzeme Kodu');
        $sheet->setCellValue('C'.$row, 'Malzeme Açıklaması');
        $sheet->setCellValue('D'.$row, 'Birim');
        $sheet->setCellValue('E'.$row, 'Birim Fiyat (iç)');
        $sheet->setCellValue('F'.$row, 'Adet');
        $sheet->setCellValue('G'.$row, 'Müş. maliyet birim');
        $sheet->setCellValue('H'.$row, 'Müş. satış birim');
        $sheet->setCellValue('I'.$row, 'Tutar');
        $sheet->getStyle('A'.$headerRow.':I'.$headerRow)->getFont()->setBold(true);
        $sheet->getStyle('A'.$headerRow.':I'.$headerRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('BBDEFB');
        $sheet->getStyle('A'.$headerRow.':I'.$headerRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $row++;
        foreach ($quote->items as $i => $item) {
            $satirTutar = (float) ($item->tutar ?? ($item->birim_fiyat * $item->adet));
            $sheet->setCellValue('A'.$row, $i + 1);
            $sheet->setCellValue('B'.$row, $item->product?->malzeme_kodu ?? '');
            $sheet->setCellValue('C'.$row, $item->product?->malzeme_aciklamasi ?? '');
            $sheet->setCellValue('D'.$row, $item->product?->birim ?? 'AD');
            $sheet->setCellValue('E'.$row, (float) $item->birim_fiyat);
            $sheet->setCellValue('F'.$row, (float) $item->adet);
            $sheet->setCellValue('G'.$row, $item->musteri_maliyet_birim !== null ? (float) $item->musteri_maliyet_birim : '');
            $sheet->setCellValue('H'.$row, $item->musteri_birim_fiyat !== null ? (float) $item->musteri_birim_fiyat : '');
            $sheet->setCellValue('I'.$row, $satirTutar);
            $sheet->getStyle('E'.$row.':I'.$row)->getNumberFormat()->setFormatCode('#.##0,00');
            $sheet->getStyle('A'.$row.':I'.$row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $row++;
        }
        $row += 2;

        $sheet->setCellValue('A'.$row, 'ÖZET');
        $sheet->mergeCells('A'.$row.':B'.$row);
        $sheet->getStyle('A'.$row)->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E3F2FD');
        $row++;
        $sheet->setCellValue('A'.$row, 'İskonto %');
        $sheet->setCellValue('B'.$row, $quote->musteri_iskonto_yuzde !== null ? (float) $quote->musteri_iskonto_yuzde : '-');
        $row++;
        $sheet->setCellValue('A'.$row, 'Müşteri net (KDV hariç, manuel)');
        $sheet->setCellValue('B'.$row, $quote->musteri_net_tutar !== null ? (float) $quote->musteri_net_tutar : '-');
        $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('#.##0,00');
        $row++;
        $sheet->setCellValue('A'.$row, 'Kalem toplamı (satır tutarları)');
        $sheet->setCellValue('B'.$row, $kalemToplam);
        $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('#.##0,00 TL');
        $row++;
        $sheet->setCellValue('A'.$row, 'Net tutar (KDV matrahı)');
        $sheet->setCellValue('B'.$row, $netKdvsiz);
        $sheet->getStyle('A'.$row.':B'.$row)->getFont()->setBold(true);
        $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('#.##0,00 TL');
        $row++;
        $sheet->setCellValue('A'.$row, 'KDV (%'.(int) round($kdvYuzde).')');
        $sheet->setCellValue('B'.$row, $kdvTutari);
        $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('#.##0,00 TL');
        $row++;
        $sheet->setCellValue('A'.$row, 'TOPLAM');
        $sheet->setCellValue('B'.$row, $genelToplam);
        $sheet->getStyle('A'.$row.':B'.$row)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('#.##0,00 TL');

        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(32);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(14);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(16);
        $sheet->getColumnDimension('H')->setWidth(16);
        $sheet->getColumnDimension('I')->setWidth(14);

        $writer = new Xlsx($spreadsheet);
        $filename = 'teklif-'.$quote->teklif_no.'.xlsx';

        return Response::streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /** Sipariş formunu Excel olarak indir – okunaklı, bölümlü yapı */
    public static function orderExcel(Order $order): StreamedResponse
    {
        $order->load(['dealer', 'items.product']);
        $kdvOran = (float) ($order->kdv_orani ?? 20);
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sipariş');

        $row = 1;
        $sheet->mergeCells('A'.$row.':B'.$row);
        $sheet->setCellValue('A'.$row, 'SİPARİŞ FORMU');
        $sheet->getStyle('A'.$row)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row += 2;

        $sheet->setCellValue('A'.$row, 'GENEL BİLGİLER');
        $sheet->mergeCells('A'.$row.':B'.$row);
        $sheet->getStyle('A'.$row)->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8F5E9');
        $row++;
        $sheet->setCellValue('A'.$row, 'Tarih');
        $sheet->setCellValue('B'.$row, $order->siparis_tarihi?->format('d.m.Y') ?? '');
        $row++;
        $sheet->setCellValue('A'.$row, 'Sipariş No');
        $sheet->setCellValue('B'.$row, $order->siparis_no);
        $row++;
        $sheet->setCellValue('A'.$row, 'Ön sipariş no');
        $sheet->setCellValue('B'.$row, $order->on_siparis_no ?? '');
        $row += 2;

        $sheet->setCellValue('A'.$row, 'FİRMA BİLGİLERİ');
        $sheet->mergeCells('A'.$row.':B'.$row);
        $sheet->getStyle('A'.$row)->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8F5E9');
        $row++;
        $sheet->setCellValue('A'.$row, 'Firma Ünvanı');
        $sheet->setCellValue('B'.$row, $order->dealer?->unvan ?? '');
        $row++;
        $sheet->setCellValue('A'.$row, 'Firma No');
        $sheet->setCellValue('B'.$row, $order->dealer?->firma_no ?? '');
        $row++;
        $sheet->setCellValue('A'.$row, 'İl/İlçe');
        $sheet->setCellValue('B'.$row, $order->dealer?->il_ilce ?? '');
        $row++;
        $sheet->setCellValue('A'.$row, 'Mail');
        $sheet->setCellValue('B'.$row, $order->dealer?->mail ?? '');
        $row++;
        $sheet->setCellValue('A'.$row, 'İlgili Kişi');
        $sheet->setCellValue('B'.$row, $order->dealer?->ilgili_kisi ?? '');
        $row++;
        $sheet->setCellValue('A'.$row, 'Tel');
        $sheet->setCellValue('B'.$row, $order->dealer?->tel ?? '');
        $row++;
        $sheet->setCellValue('A'.$row, 'Proje Adı');
        $sheet->setCellValue('B'.$row, $order->proje_adi ?? '');
        $row++;
        $sheet->setCellValue('A'.$row, 'Cihaz Marka - Model');
        $sheet->setCellValue('B'.$row, $order->cihaz_marka_model ?? '');
        $row += 2;

        $sheet->setCellValue('A'.$row, 'TEKNİK / ÖZELLİK');
        $sheet->mergeCells('A'.$row.':B'.$row);
        $sheet->getStyle('A'.$row)->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8F5E9');
        $row++;
        $sheet->setCellValue('A'.$row, 'Baca çapı (mm)');
        $sheet->setCellValue('B'.$row, $order->bac_cap_mm !== null ? Order::formatMmForDisplay($order->bac_cap_mm, '') : '');
        $row++;
        $sheet->setCellValue('A'.$row, 'Baca yüksekliği (mm)');
        $sheet->setCellValue('B'.$row, $order->bac_yukseklik_mm !== null ? Order::formatMmForDisplay($order->bac_yukseklik_mm, '') : '');
        $row++;
        $sheet->setCellValue('A'.$row, 'Yön');
        $sheet->setCellValue('B'.$row, self::orderYonEtiketi($order->yon) ?: '–');
        $row++;
        $sheet->setCellValue('A'.$row, 'Çizim / form özellikleri');
        $oz = self::orderOzelliklerMetni($order);
        $sheet->setCellValue('B'.$row, $oz !== '' ? $oz : '–');
        $row += 2;

        $sheet->setCellValue('A'.$row, 'KALEMLER');
        $sheet->mergeCells('A'.$row.':J'.$row);
        $sheet->getStyle('A'.$row)->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8F5E9');
        $row++;
        $headerRow = $row;
        $sheet->setCellValue('A'.$row, 'No');
        $sheet->setCellValue('B'.$row, 'Malzeme Kodu');
        $sheet->setCellValue('C'.$row, 'Malzeme Açıklaması');
        $sheet->setCellValue('D'.$row, 'Birim');
        $sheet->setCellValue('E'.$row, 'Birim Fiyat');
        $sheet->setCellValue('F'.$row, 'Adet');
        $sheet->setCellValue('G'.$row, 'Tutar');
        $sheet->setCellValue('H'.$row, 'Karşı teklif birim');
        $sheet->setCellValue('I'.$row, 'Uzunluk (m)');
        $sheet->setCellValue('J'.$row, 'Sac kalınlık');
        $sheet->getStyle('A'.$headerRow.':J'.$headerRow)->getFont()->setBold(true);
        $sheet->getStyle('A'.$headerRow.':J'.$headerRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('C8E6C9');
        $sheet->getStyle('A'.$headerRow.':J'.$headerRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $row++;
        foreach ($order->items as $i => $item) {
            $satirTutar = (float) ($item->tutar ?? ($item->birim_fiyat * $item->adet));
            $sheet->setCellValue('A'.$row, $i + 1);
            $sheet->setCellValue('B'.$row, $item->product?->malzeme_kodu ?? '');
            $sheet->setCellValue('C'.$row, $item->product?->malzeme_aciklamasi ?? '');
            $sheet->setCellValue('D'.$row, $item->product?->birim ?? 'AD');
            $sheet->setCellValue('E'.$row, (float) $item->birim_fiyat);
            $sheet->setCellValue('F'.$row, (float) $item->adet);
            $sheet->setCellValue('G'.$row, $satirTutar);
            $sheet->setCellValue('H'.$row, $item->bayi_karsi_birim_fiyat !== null ? (float) $item->bayi_karsi_birim_fiyat : '');
            $sheet->setCellValue('I'.$row, $item->product?->uzunluk_m !== null ? (float) $item->product->uzunluk_m : '');
            $sheet->setCellValue('J'.$row, $item->product?->sac_kalinlik !== null ? (float) $item->product->sac_kalinlik : '');
            $sheet->getStyle('E'.$row.':H'.$row)->getNumberFormat()->setFormatCode('#.##0,00');
            $sheet->getStyle('I'.$row.':J'.$row)->getNumberFormat()->setFormatCode('#.##0,0000');
            $sheet->getStyle('A'.$row.':J'.$row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $row++;
        }
        $row += 2;

        $sheet->setCellValue('A'.$row, 'HESAP ÖZETİ');
        $sheet->mergeCells('A'.$row.':B'.$row);
        $sheet->getStyle('A'.$row)->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8F5E9');
        $row++;
        $sheet->setCellValue('A'.$row, 'İskonto %');
        $sheet->setCellValue('B'.$row, $order->iskonto_yuzde !== null ? (float) $order->iskonto_yuzde : '-');
        $row++;
        $sheet->setCellValue('A'.$row, 'Kalem toplamı (iskonto sonrası, KDV hariç)');
        $sheet->setCellValue('B'.$row, $order->kalem_net_kdvsiz);
        $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('#.##0,00 TL');
        $row++;
        $sheet->setCellValue('A'.$row, 'Ön tutar (hesaplanan, KDV hariç)');
        $sheet->setCellValue('B'.$row, $order->hesaplanan_kdvsiz_on);
        $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('#.##0,00 TL');
        $row++;
        $sheet->setCellValue('A'.$row, 'Nihai taban (kur farkı sonrası, KDV hariç)');
        $sheet->setCellValue('B'.$row, $order->hesaplanan_kdvsiz_nihai);
        $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('#.##0,00 TL');
        $row++;
        $sheet->setCellValue('A'.$row, 'Ara toplam (KDV hariç)');
        $sheet->setCellValue('B'.$row, $order->ara_toplam_kdvsiz);
        $sheet->getStyle('A'.$row.':B'.$row)->getFont()->setBold(true);
        $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('#.##0,00 TL');
        $row++;
        $kdvEtiket = abs($kdvOran - round($kdvOran)) < 0.001 ? (string) (int) round($kdvOran) : number_format($kdvOran, 2, '.', '');
        $sheet->setCellValue('A'.$row, 'KDV (%'.$kdvEtiket.')');
        $sheet->setCellValue('B'.$row, $order->kdv_tutari);
        $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('#.##0,00 TL');
        $row++;
        $sheet->setCellValue('A'.$row, 'GENEL TOPLAM');
        $sheet->setCellValue('B'.$row, $order->genel_toplam);
        $sheet->getStyle('A'.$row.':B'.$row)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('#.##0,00 TL');

        $sheet->getColumnDimension('A')->setWidth(22);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(28);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(8);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(14);
        $sheet->getColumnDimension('I')->setWidth(12);
        $sheet->getColumnDimension('J')->setWidth(12);

        $writer = new Xlsx($spreadsheet);
        $filename = 'siparis-'.$order->siparis_no.'.xlsx';

        return Response::streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /** Teklif formunu CSV olarak indir – bölümlü, okunaklı yapı */
    public static function quoteCsv(Quote $quote): StreamedResponse
    {
        $quote->load(['dealer', 'items.product']);
        $kalemToplam = (float) $quote->items->sum(fn ($i) => (float) ($i->tutar ?? ($i->birim_fiyat * $i->adet)));
        $netKdvsiz = self::quoteNetKdvsizForKdv($quote);
        $kdvYuzde = self::quoteKdvOraniYuzde();
        $kdvTutari = round($netKdvsiz * ($kdvYuzde / 100), 4);
        $genelToplam = round($netKdvsiz + $kdvTutari, 4);
        $filename = 'teklif-'.$quote->teklif_no.'.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];
        $fmt = fn ($n) => number_format((float) $n, 2, ',', '.');
        $callback = function () use ($quote, $kalemToplam, $netKdvsiz, $kdvYuzde, $kdvTutari, $genelToplam, $fmt) {
            $out = fopen('php://output', 'w');
            fprintf($out, "\xEF\xBB\xBF");
            fputcsv($out, ['SİPARİŞ / TEKLİF FORMU'], ';');
            fputcsv($out, [''], ';');
            fputcsv($out, ['=== GENEL BİLGİLER ==='], ';');
            fputcsv($out, ['Tarih', $quote->created_at?->format('d.m.Y') ?? ''], ';');
            fputcsv($out, ['Teklif No', $quote->teklif_no], ';');
            fputcsv($out, [''], ';');
            fputcsv($out, ['=== FİRMA BİLGİLERİ ==='], ';');
            fputcsv($out, ['Firma Ünvanı', $quote->dealer?->unvan ?? ''], ';');
            fputcsv($out, ['Firma No', $quote->dealer?->firma_no ?? ''], ';');
            fputcsv($out, ['İl/İlçe', $quote->dealer?->il_ilce ?? ''], ';');
            fputcsv($out, ['Mail', $quote->dealer?->mail ?? ''], ';');
            fputcsv($out, ['İlgili Kişi', $quote->dealer?->ilgili_kisi ?? ''], ';');
            fputcsv($out, ['Tel', $quote->dealer?->tel ?? ''], ';');
            fputcsv($out, ['Proje Adı', $quote->proje_adi ?? ''], ';');
            fputcsv($out, ['Cihaz Marka - Model', $quote->cihaz_marka_model ?? ''], ';');
            fputcsv($out, [''], ';');
            fputcsv($out, ['=== KALEMLER ==='], ';');
            fputcsv($out, ['No', 'Malzeme Kodu', 'Malzeme Açıklaması', 'Birim', 'Birim Fiyat (iç)', 'Adet', 'Müş. maliyet birim', 'Müş. satış birim', 'Tutar'], ';');
            foreach ($quote->items as $i => $item) {
                $satirTutar = (float) ($item->tutar ?? ($item->birim_fiyat * $item->adet));
                fputcsv($out, [
                    $i + 1,
                    $item->product?->malzeme_kodu ?? '',
                    $item->product?->malzeme_aciklamasi ?? '',
                    $item->product?->birim ?? 'AD',
                    $fmt($item->birim_fiyat),
                    $fmt($item->adet),
                    $item->musteri_maliyet_birim !== null ? $fmt($item->musteri_maliyet_birim) : '',
                    $item->musteri_birim_fiyat !== null ? $fmt($item->musteri_birim_fiyat) : '',
                    $fmt($satirTutar),
                ], ';');
            }
            fputcsv($out, [''], ';');
            fputcsv($out, ['=== ÖZET ==='], ';');
            fputcsv($out, ['İskonto %', $quote->musteri_iskonto_yuzde !== null ? $fmt($quote->musteri_iskonto_yuzde) : '-'], ';');
            fputcsv($out, ['Müşteri net (KDV hariç, manuel)', $quote->musteri_net_tutar !== null ? $fmt($quote->musteri_net_tutar).' TL' : '-'], ';');
            fputcsv($out, ['Kalem toplamı (satır tutarları)', $fmt($kalemToplam).' TL'], ';');
            fputcsv($out, ['Net tutar (KDV matrahı)', $fmt($netKdvsiz).' TL'], ';');
            fputcsv($out, ['KDV (%'.(int) round($kdvYuzde).')', $fmt($kdvTutari).' TL'], ';');
            fputcsv($out, ['TOPLAM', $fmt($genelToplam).' TL'], ';');
            fclose($out);
        };

        return Response::stream($callback, 200, $headers);
    }

    /** Sipariş formunu CSV olarak indir – bölümlü, okunaklı yapı */
    public static function orderCsv(Order $order): StreamedResponse
    {
        $order->load(['dealer', 'items.product']);
        $kdvOran = (float) ($order->kdv_orani ?? 20);
        $filename = 'siparis-'.$order->siparis_no.'.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];
        $fmt = fn ($n) => number_format((float) $n, 2, ',', '.');
        $fmt4 = fn ($n) => number_format((float) $n, 4, ',', '.');
        $callback = function () use ($order, $kdvOran, $fmt, $fmt4) {
            $out = fopen('php://output', 'w');
            $ozellikCsv = self::orderOzelliklerMetni($order);
            fprintf($out, "\xEF\xBB\xBF");
            fputcsv($out, ['SİPARİŞ FORMU'], ';');
            fputcsv($out, [''], ';');
            fputcsv($out, ['=== GENEL BİLGİLER ==='], ';');
            fputcsv($out, ['Tarih', $order->siparis_tarihi?->format('d.m.Y') ?? ''], ';');
            fputcsv($out, ['Sipariş No', $order->siparis_no], ';');
            fputcsv($out, ['Ön sipariş no', $order->on_siparis_no ?? ''], ';');
            fputcsv($out, [''], ';');
            fputcsv($out, ['=== FİRMA BİLGİLERİ ==='], ';');
            fputcsv($out, ['Firma Ünvanı', $order->dealer?->unvan ?? ''], ';');
            fputcsv($out, ['Firma No', $order->dealer?->firma_no ?? ''], ';');
            fputcsv($out, ['İl/İlçe', $order->dealer?->il_ilce ?? ''], ';');
            fputcsv($out, ['Mail', $order->dealer?->mail ?? ''], ';');
            fputcsv($out, ['İlgili Kişi', $order->dealer?->ilgili_kisi ?? ''], ';');
            fputcsv($out, ['Tel', $order->dealer?->tel ?? ''], ';');
            fputcsv($out, ['Proje Adı', $order->proje_adi ?? ''], ';');
            fputcsv($out, ['Cihaz Marka - Model', $order->cihaz_marka_model ?? ''], ';');
            fputcsv($out, [''], ';');
            fputcsv($out, ['=== TEKNİK / ÖZELLİK ==='], ';');
            fputcsv($out, ['Baca çapı (mm)', $order->bac_cap_mm !== null ? Order::formatMmForDisplay($order->bac_cap_mm, '') : ''], ';');
            fputcsv($out, ['Baca yüksekliği (mm)', $order->bac_yukseklik_mm !== null ? Order::formatMmForDisplay($order->bac_yukseklik_mm, '') : ''], ';');
            fputcsv($out, ['Yön', self::orderYonEtiketi($order->yon) ?: '–'], ';');
            fputcsv($out, ['Çizim / form özellikleri', $ozellikCsv !== '' ? $ozellikCsv : '–'], ';');
            fputcsv($out, [''], ';');
            fputcsv($out, ['=== KALEMLER ==='], ';');
            fputcsv($out, ['No', 'Malzeme Kodu', 'Malzeme Açıklaması', 'Birim', 'Birim Fiyat', 'Adet', 'Tutar', 'Karşı teklif birim', 'Uzunluk (m)', 'Sac kalınlık'], ';');
            foreach ($order->items as $i => $item) {
                $satirTutar = (float) ($item->tutar ?? ($item->birim_fiyat * $item->adet));
                fputcsv($out, [
                    $i + 1,
                    $item->product?->malzeme_kodu ?? '',
                    $item->product?->malzeme_aciklamasi ?? '',
                    $item->product?->birim ?? 'AD',
                    $fmt($item->birim_fiyat),
                    $fmt($item->adet),
                    $fmt($satirTutar),
                    $item->bayi_karsi_birim_fiyat !== null ? $fmt($item->bayi_karsi_birim_fiyat) : '',
                    $item->product?->uzunluk_m !== null ? $fmt4($item->product->uzunluk_m) : '',
                    $item->product?->sac_kalinlik !== null ? $fmt4($item->product->sac_kalinlik) : '',
                ], ';');
            }
            fputcsv($out, [''], ';');
            fputcsv($out, ['=== HESAP ÖZETİ ==='], ';');
            fputcsv($out, ['İskonto %', $order->iskonto_yuzde !== null ? $fmt($order->iskonto_yuzde) : '-'], ';');
            fputcsv($out, ['Kalem toplamı (iskonto sonrası, KDV hariç)', $fmt($order->kalem_net_kdvsiz).' TL'], ';');
            fputcsv($out, ['Ön tutar (hesaplanan, KDV hariç)', $fmt($order->hesaplanan_kdvsiz_on).' TL'], ';');
            fputcsv($out, ['Nihai taban (kur farkı sonrası, KDV hariç)', $fmt($order->hesaplanan_kdvsiz_nihai).' TL'], ';');
            fputcsv($out, ['Ara toplam (KDV hariç)', $fmt($order->ara_toplam_kdvsiz).' TL'], ';');
            $kdvEtiket = abs($kdvOran - round($kdvOran)) < 0.001 ? (string) (int) round($kdvOran) : number_format($kdvOran, 2, '.', '');
            fputcsv($out, ['KDV (%'.$kdvEtiket.')', $fmt($order->kdv_tutari).' TL'], ';');
            fputcsv($out, ['GENEL TOPLAM', $fmt($order->genel_toplam).' TL'], ';');
            fclose($out);
        };

        return Response::stream($callback, 200, $headers);
    }

    /** Teklif: KDV matrahı (KDV hariç net tutar). */
    public static function quoteNetKdvsizForKdv(Quote $q): float
    {
        $q->loadMissing('items');
        if ($q->musteri_net_tutar !== null) {
            return round((float) $q->musteri_net_tutar, 4);
        }
        $kalemToplam = (float) $q->items->sum(fn ($i) => (float) ($i->tutar ?? 0));
        if ($q->musteri_iskonto_yuzde !== null && (float) $q->musteri_iskonto_yuzde > 0) {
            return round($kalemToplam * (1 - (float) $q->musteri_iskonto_yuzde / 100), 4);
        }

        return round($kalemToplam, 4);
    }

    public static function quoteKdvOraniYuzde(): float
    {
        return 20.0;
    }

    public static function orderYonEtiketi(?string $yon): string
    {
        return match ($yon) {
            'yatay' => 'Yatay',
            'dikey' => 'Dikey',
            null, '' => '',
            default => (string) $yon,
        };
    }

    /** Sipariş çizim/form özellik kodları (işaretli olanlar). */
    public static function orderOzelliklerMetni(Order $order): string
    {
        $parca = [];
        foreach (Order::ozellikKoduAciklamalari() as $attr => $metin) {
            if ($order->{$attr}) {
                $parca[] = $metin;
            }
        }

        return $parca === [] ? '' : implode('; ', $parca);
    }
}
