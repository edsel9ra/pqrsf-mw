<?php

use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\PqrsfController;
use App\Http\Controllers\PqrsfSubmissionPdfController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('pqrsf.create');
});

Route::prefix('pqrsf')->name('pqrsf.')->group(function () {
    Route::get('/', [PqrsfController::class, 'create'])->name('create');
    Route::post('/', [PqrsfController::class, 'store'])->middleware('throttle:5,1')->name('store');
    Route::get('/gracias', [PqrsfController::class, 'gracias'])->name('gracias');
});

Route::get('/pqrsf-submissions/{submission}/pdf', [PqrsfSubmissionPdfController::class, 'show'])
    ->middleware('signed')
    ->name('pqrsf.submissions.pdf');

Route::middleware(['web', 'auth', 'can:access-admin'])->prefix('admin/reportes')->name('admin.reportes.')->group(function () {
    Route::get('/pdf', [ReportController::class, 'pdf'])->name('pdf');
    Route::get('/registros/pdf', [ReportController::class, 'submissionsPdf'])->name('registros.pdf');
    Route::get('/registros/xlsx', [ReportController::class, 'submissionsXlsx'])->name('registros.xlsx');
    Route::get('/observaciones/pdf', [ReportController::class, 'observationsPdf'])->name('observaciones.pdf');
    Route::get('/observaciones/xlsx', [ReportController::class, 'observationsXlsx'])->name('observaciones.xlsx');
});
