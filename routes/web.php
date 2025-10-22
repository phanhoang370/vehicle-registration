<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterCarController;
use App\Http\Controllers\ImportExcelController;

Route::get('/', function () {
    return view('welcome');
});

// Route::resource('register-car', RegisterCarController::class);
Route::prefix('register-car')->name('register-car.')->group(function () {
    Route::get('/', [RegisterCarController::class, 'index'])->name('index');
    Route::get('/list', [RegisterCarController::class, 'showList'])->name('car-list');

    Route::get('/import', [ImportExcelController::class, 'showImportForm'])->name('import');
    Route::post('/import', [ImportExcelController::class, 'import'])->name('import-process');
    Route::get('/download-template', [ImportExcelController::class, 'downloadTemplate'])->name('download-template');
    Route::get('/download-error/{fileName}', [ImportExcelController::class, 'downloadErrorFile'])->name('download-error');

    Route::get('/get-list', [RegisterCarController::class, 'getList'])->name('get-list');
    Route::get('/create', [RegisterCarController::class, 'create'])->name('create');
    Route::get('/create-by-excel', [RegisterCarController::class, 'createByExcel'])->name('create-excel');
    Route::post('/store', [RegisterCarController::class, 'store'])->name('store');
    Route::get('/{id}', [RegisterCarController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [RegisterCarController::class, 'edit'])->name('edit');
    Route::put('/{id}', [RegisterCarController::class, 'update'])->name('update');
    Route::delete('/{id}', [RegisterCarController::class, 'destroy'])->name('destroy');
    
    // Route kiểm tra xe đã đăng ký
    Route::post('/check-truck', [RegisterCarController::class, 'checkTruck'])->name('check-truck');
    Route::post('/import-file', [RegisterCarController::class, 'import'])->name('import-file');

   


});