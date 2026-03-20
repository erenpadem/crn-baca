<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Quote;
use App\Services\FormExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FormExportController extends Controller
{
    public function quoteExcel(int $id): StreamedResponse
    {
        $quote = Quote::with(['dealer', 'items.product'])->findOrFail($id);
        return FormExportService::quoteExcel($quote);
    }

    public function quoteCsv(int $id): StreamedResponse
    {
        $quote = Quote::with(['dealer', 'items.product'])->findOrFail($id);
        return FormExportService::quoteCsv($quote);
    }

    public function quotePdf(int $id): Response|StreamedResponse
    {
        $quote = Quote::with(['dealer', 'items.product'])->findOrFail($id);
        return FormExportService::quotePdf($quote);
    }

    public function orderExcel(int $id): StreamedResponse
    {
        $order = Order::with(['dealer', 'items.product'])->findOrFail($id);
        return FormExportService::orderExcel($order);
    }

    public function orderCsv(int $id): StreamedResponse
    {
        $order = Order::with(['dealer', 'items.product'])->findOrFail($id);
        return FormExportService::orderCsv($order);
    }

    public function orderPdf(int $id): Response|StreamedResponse
    {
        $order = Order::with(['dealer', 'items.product'])->findOrFail($id);
        return FormExportService::orderPdf($order);
    }
}
