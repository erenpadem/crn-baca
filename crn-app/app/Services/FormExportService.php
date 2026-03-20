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
        $pdf->setPaper('a4', 'portrait');
        return $pdf->stream('teklif-' . $quote->teklif_no . '.pdf', ['Attachment' => true]);
    }

    /** Sipariş formunu PDF olarak indir */
    public static function orderPdf(Order $order): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = Pdf::loadView('exports.siparis-formu', ['order' => $order->load(['dealer', 'items.product'])]);
        $pdf->setPaper('a4', 'portrait');
        return $pdf->stream('siparis-' . $order->siparis_no . '.pdf', ['Attachment' => true]);
    }

    /** Teklif formunu Excel olarak indir – okunaklı, bölümlü yapı */
    public static function quoteExcel(Quote $quote): StreamedResponse
    {
        $quote->load(['dealer', 'items.product']);
        $araToplam = $quote->items->sum(fn ($i) => (float) ($i->tutar ?? ($i->birim_fiyat * $i->adet)));
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Teklif');

        $row = 1;
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->setCellValue('A' . $row, 'SİPARİŞ / TEKLİF FORMU');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row += 2;

        $sheet->setCellValue('A' . $row, 'GENEL BİLGİLER');
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E3F2FD');
        $row++;
        $sheet->setCellValue('A' . $row, 'Tarih');
        $sheet->setCellValue('B' . $row, $quote->created_at?->format('d.m.Y') ?? '');
        $row++;
        $sheet->setCellValue('A' . $row, 'Teklif No');
        $sheet->setCellValue('B' . $row, $quote->teklif_no);
        $row += 2;

        $sheet->setCellValue('A' . $row, 'FİRMA BİLGİLERİ');
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E3F2FD');
        $row++;
        $sheet->setCellValue('A' . $row, 'Firma Ünvanı');
        $sheet->setCellValue('B' . $row, $quote->dealer?->unvan ?? '');
        $row++;
        $sheet->setCellValue('A' . $row, 'Firma No');
        $sheet->setCellValue('B' . $row, $quote->dealer?->firma_no ?? '');
        $row++;
        $sheet->setCellValue('A' . $row, 'İl/İlçe');
        $sheet->setCellValue('B' . $row, $quote->dealer?->il_ilce ?? '');
        $row++;
        $sheet->setCellValue('A' . $row, 'Mail');
        $sheet->setCellValue('B' . $row, $quote->dealer?->mail ?? '');
        $row++;
        $sheet->setCellValue('A' . $row, 'İlgili Kişi');
        $sheet->setCellValue('B' . $row, $quote->dealer?->ilgili_kisi ?? '');
        $row++;
        $sheet->setCellValue('A' . $row, 'Tel');
        $sheet->setCellValue('B' . $row, $quote->dealer?->tel ?? '');
        $row++;
        $sheet->setCellValue('A' . $row, 'Proje Adı');
        $sheet->setCellValue('B' . $row, $quote->proje_adi ?? '');
        $row++;
        $sheet->setCellValue('A' . $row, 'Cihaz Marka - Model');
        $sheet->setCellValue('B' . $row, $quote->cihaz_marka_model ?? '');
        $row += 2;

        $sheet->setCellValue('A' . $row, 'KALEMLER');
        $sheet->mergeCells('A' . $row . ':G' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E3F2FD');
        $row++;
        $headerRow = $row;
        $sheet->setCellValue('A' . $row, 'No');
        $sheet->setCellValue('B' . $row, 'Malzeme Kodu');
        $sheet->setCellValue('C' . $row, 'Malzeme Açıklaması');
        $sheet->setCellValue('D' . $row, 'Birim');
        $sheet->setCellValue('E' . $row, 'Birim Fiyat');
        $sheet->setCellValue('F' . $row, 'Adet');
        $sheet->setCellValue('G' . $row, 'Tutar');
        $sheet->getStyle('A' . $headerRow . ':G' . $headerRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $headerRow . ':G' . $headerRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('BBDEFB');
        $sheet->getStyle('A' . $headerRow . ':G' . $headerRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $row++;
        foreach ($quote->items as $i => $item) {
            $satirTutar = (float) ($item->tutar ?? ($item->birim_fiyat * $item->adet));
            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $item->product?->malzeme_kodu ?? '');
            $sheet->setCellValue('C' . $row, $item->product?->malzeme_aciklamasi ?? '');
            $sheet->setCellValue('D' . $row, $item->product?->birim ?? 'AD');
            $sheet->setCellValue('E' . $row, (float) $item->birim_fiyat);
            $sheet->setCellValue('F' . $row, (float) $item->adet);
            $sheet->setCellValue('G' . $row, $satirTutar);
            $sheet->getStyle('E' . $row . ':G' . $row)->getNumberFormat()->setFormatCode('#.##0,00');
            $sheet->getStyle('A' . $row . ':G' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $row++;
        }
        $row += 2;

        $sheet->setCellValue('A' . $row, 'ÖZET');
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E3F2FD');
        $row++;
        $sheet->setCellValue('A' . $row, 'İskonto %');
        $sheet->setCellValue('B' . $row, $quote->musteri_iskonto_yuzde !== null ? (float) $quote->musteri_iskonto_yuzde : '-');
        $row++;
        $sheet->setCellValue('A' . $row, 'Ara Toplam');
        $sheet->setCellValue('B' . $row, $araToplam);
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#.##0,00 TL');
        $row++;
        $sheet->setCellValue('A' . $row, 'KDV (%18)');
        $sheet->setCellValue('B' . $row, $araToplam * 0.18);
        $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#.##0,00 TL');
        $row++;
        $sheet->setCellValue('A' . $row, 'TOPLAM');
        $sheet->setCellValue('B' . $row, $araToplam * 1.18);
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#.##0,00 TL');

        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(35);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(14);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(14);

        $writer = new Xlsx($spreadsheet);
        $filename = 'teklif-' . $quote->teklif_no . '.xlsx';
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
        $araToplam = $order->items->sum(fn ($i) => (float) ($i->tutar ?? ($i->birim_fiyat * $i->adet)));
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sipariş');

        $row = 1;
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->setCellValue('A' . $row, 'SİPARİŞ FORMU');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row += 2;

        $sheet->setCellValue('A' . $row, 'GENEL BİLGİLER');
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8F5E9');
        $row++;
        $sheet->setCellValue('A' . $row, 'Tarih');
        $sheet->setCellValue('B' . $row, $order->siparis_tarihi?->format('d.m.Y') ?? '');
        $row++;
        $sheet->setCellValue('A' . $row, 'Sipariş No');
        $sheet->setCellValue('B' . $row, $order->siparis_no);
        $row += 2;

        $sheet->setCellValue('A' . $row, 'FİRMA BİLGİLERİ');
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8F5E9');
        $row++;
        $sheet->setCellValue('A' . $row, 'Firma Ünvanı');
        $sheet->setCellValue('B' . $row, $order->dealer?->unvan ?? '');
        $row++;
        $sheet->setCellValue('A' . $row, 'Firma No');
        $sheet->setCellValue('B' . $row, $order->dealer?->firma_no ?? '');
        $row++;
        $sheet->setCellValue('A' . $row, 'İl/İlçe');
        $sheet->setCellValue('B' . $row, $order->dealer?->il_ilce ?? '');
        $row++;
        $sheet->setCellValue('A' . $row, 'Mail');
        $sheet->setCellValue('B' . $row, $order->dealer?->mail ?? '');
        $row++;
        $sheet->setCellValue('A' . $row, 'İlgili Kişi');
        $sheet->setCellValue('B' . $row, $order->dealer?->ilgili_kisi ?? '');
        $row++;
        $sheet->setCellValue('A' . $row, 'Tel');
        $sheet->setCellValue('B' . $row, $order->dealer?->tel ?? '');
        $row++;
        $sheet->setCellValue('A' . $row, 'Proje Adı');
        $sheet->setCellValue('B' . $row, $order->proje_adi ?? '');
        $row++;
        $sheet->setCellValue('A' . $row, 'Cihaz Marka - Model');
        $sheet->setCellValue('B' . $row, $order->cihaz_marka_model ?? '');
        $row += 2;

        $sheet->setCellValue('A' . $row, 'KALEMLER');
        $sheet->mergeCells('A' . $row . ':G' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8F5E9');
        $row++;
        $headerRow = $row;
        $sheet->setCellValue('A' . $row, 'No');
        $sheet->setCellValue('B' . $row, 'Malzeme Kodu');
        $sheet->setCellValue('C' . $row, 'Malzeme Açıklaması');
        $sheet->setCellValue('D' . $row, 'Birim');
        $sheet->setCellValue('E' . $row, 'Birim Fiyat');
        $sheet->setCellValue('F' . $row, 'Adet');
        $sheet->setCellValue('G' . $row, 'Tutar');
        $sheet->getStyle('A' . $headerRow . ':G' . $headerRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $headerRow . ':G' . $headerRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('C8E6C9');
        $sheet->getStyle('A' . $headerRow . ':G' . $headerRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $row++;
        foreach ($order->items as $i => $item) {
            $satirTutar = (float) ($item->tutar ?? ($item->birim_fiyat * $item->adet));
            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $item->product?->malzeme_kodu ?? '');
            $sheet->setCellValue('C' . $row, $item->product?->malzeme_aciklamasi ?? '');
            $sheet->setCellValue('D' . $row, $item->product?->birim ?? 'AD');
            $sheet->setCellValue('E' . $row, (float) $item->birim_fiyat);
            $sheet->setCellValue('F' . $row, (float) $item->adet);
            $sheet->setCellValue('G' . $row, $satirTutar);
            $sheet->getStyle('E' . $row . ':G' . $row)->getNumberFormat()->setFormatCode('#.##0,00');
            $sheet->getStyle('A' . $row . ':G' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $row++;
        }
        $row += 2;

        $sheet->setCellValue('A' . $row, 'ÖZET');
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8F5E9');
        $row++;
        $sheet->setCellValue('A' . $row, 'İskonto %');
        $sheet->setCellValue('B' . $row, $order->iskonto_yuzde !== null ? (float) $order->iskonto_yuzde : '-');
        $row++;
        $sheet->setCellValue('A' . $row, 'Ara Toplam');
        $sheet->setCellValue('B' . $row, $araToplam);
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#.##0,00 TL');
        $row++;
        $sheet->setCellValue('A' . $row, 'KDV (%18)');
        $sheet->setCellValue('B' . $row, $araToplam * 0.18);
        $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#.##0,00 TL');
        $row++;
        $sheet->setCellValue('A' . $row, 'TOPLAM');
        $sheet->setCellValue('B' . $row, $araToplam * 1.18);
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#.##0,00 TL');

        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(35);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(14);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(14);

        $writer = new Xlsx($spreadsheet);
        $filename = 'siparis-' . $order->siparis_no . '.xlsx';
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
        $araToplam = $quote->items->sum(fn ($i) => (float) ($i->tutar ?? ($i->birim_fiyat * $i->adet)));
        $filename = 'teklif-' . $quote->teklif_no . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        $fmt = fn ($n) => number_format((float) $n, 2, ',', '.');
        $callback = function () use ($quote, $araToplam, $fmt) {
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
            fputcsv($out, ['No', 'Malzeme Kodu', 'Malzeme Açıklaması', 'Birim', 'Birim Fiyat', 'Adet', 'Tutar'], ';');
            foreach ($quote->items as $i => $item) {
                $satirTutar = (float) ($item->tutar ?? ($item->birim_fiyat * $item->adet));
                fputcsv($out, [
                    $i + 1,
                    $item->product?->malzeme_kodu ?? '',
                    $item->product?->malzeme_aciklamasi ?? '',
                    $item->product?->birim ?? 'AD',
                    $fmt($item->birim_fiyat),
                    $fmt($item->adet),
                    $fmt($satirTutar),
                ], ';');
            }
            fputcsv($out, [''], ';');
            fputcsv($out, ['=== ÖZET ==='], ';');
            fputcsv($out, ['İskonto %', $quote->musteri_iskonto_yuzde !== null ? $fmt($quote->musteri_iskonto_yuzde) : '-'], ';');
            fputcsv($out, ['Ara Toplam', $fmt($araToplam) . ' TL'], ';');
            fputcsv($out, ['KDV (%18)', $fmt($araToplam * 0.18) . ' TL'], ';');
            fputcsv($out, ['TOPLAM', $fmt($araToplam * 1.18) . ' TL'], ';');
            fclose($out);
        };
        return Response::stream($callback, 200, $headers);
    }

    /** Sipariş formunu CSV olarak indir – bölümlü, okunaklı yapı */
    public static function orderCsv(Order $order): StreamedResponse
    {
        $order->load(['dealer', 'items.product']);
        $araToplam = $order->items->sum(fn ($i) => (float) ($i->tutar ?? ($i->birim_fiyat * $i->adet)));
        $filename = 'siparis-' . $order->siparis_no . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        $fmt = fn ($n) => number_format((float) $n, 2, ',', '.');
        $callback = function () use ($order, $araToplam, $fmt) {
            $out = fopen('php://output', 'w');
            fprintf($out, "\xEF\xBB\xBF");
            fputcsv($out, ['SİPARİŞ FORMU'], ';');
            fputcsv($out, [''], ';');
            fputcsv($out, ['=== GENEL BİLGİLER ==='], ';');
            fputcsv($out, ['Tarih', $order->siparis_tarihi?->format('d.m.Y') ?? ''], ';');
            fputcsv($out, ['Sipariş No', $order->siparis_no], ';');
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
            fputcsv($out, ['=== KALEMLER ==='], ';');
            fputcsv($out, ['No', 'Malzeme Kodu', 'Malzeme Açıklaması', 'Birim', 'Birim Fiyat', 'Adet', 'Tutar'], ';');
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
                ], ';');
            }
            fputcsv($out, [''], ';');
            fputcsv($out, ['=== ÖZET ==='], ';');
            fputcsv($out, ['İskonto %', $order->iskonto_yuzde !== null ? $fmt($order->iskonto_yuzde) : '-'], ';');
            fputcsv($out, ['Ara Toplam', $fmt($araToplam) . ' TL'], ';');
            fputcsv($out, ['KDV (%18)', $fmt($araToplam * 0.18) . ' TL'], ';');
            fputcsv($out, ['TOPLAM', $fmt($araToplam * 1.18) . ' TL'], ';');
            fclose($out);
        };
        return Response::stream($callback, 200, $headers);
    }
}
