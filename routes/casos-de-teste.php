<?php

use App\Http\Controllers\CasoDeTesteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('casos-de-teste', [CasoDeTesteController::class, 'index'])->name('casos-de-teste.index');
    Route::get('casos-de-teste/criar', [CasoDeTesteController::class, 'create'])->name('casos-de-teste.create');
    Route::post('casos-de-teste', [CasoDeTesteController::class, 'store'])->name('casos-de-teste.store');
    Route::get('casos-de-teste/{casoDeTeste}/editar', [CasoDeTesteController::class, 'edit'])->name('casos-de-teste.edit');
    Route::patch('casos-de-teste/{casoDeTeste}', [CasoDeTesteController::class, 'update'])->name('casos-de-teste.update');
    Route::delete('casos-de-teste/{casoDeTeste}', [CasoDeTesteController::class, 'destroy'])->name('casos-de-teste.destroy');
});
