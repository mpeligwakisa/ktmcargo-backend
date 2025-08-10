<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CargoController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ReportController;

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('cargo', CargoController::class);
    Route::resource('clients', ClientController::class);
});



Route::get('/export/pdf', [ReportController::class, 'exportPDF']);
Route::get('/export/excel', [ReportController::class, 'exportExcel']);
Route::get('/export/csv', [ReportController::class, 'exportCSV']);

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');



require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
