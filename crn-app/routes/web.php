<?php

use App\Http\Controllers\FormExportController;
use Illuminate\Support\Facades\Route;

Route::redirect('/bayi', '/musteri');
Route::redirect('/bayi/login', '/musteri/login');

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->prefix('admin/export')->group(function () {
    Route::get('quote/{id}/excel', [FormExportController::class, 'quoteExcel'])->name('admin.quotes.export-excel');
    Route::get('quote/{id}/csv', [FormExportController::class, 'quoteCsv'])->name('admin.quotes.export-csv');
    Route::get('quote/{id}/pdf', [FormExportController::class, 'quotePdf'])->name('admin.quotes.export-pdf');
    Route::get('order/{id}/excel', [FormExportController::class, 'orderExcel'])->name('admin.orders.export-excel');
    Route::get('order/{id}/csv', [FormExportController::class, 'orderCsv'])->name('admin.orders.export-csv');
    Route::get('order/{id}/pdf', [FormExportController::class, 'orderPdf'])->name('admin.orders.export-pdf');
});
