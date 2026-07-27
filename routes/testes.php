<?php

use App\Http\Controllers\CenarioController;
use App\Http\Controllers\TesteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('testes', [TesteController::class, 'index'])->name('testes.index');
    Route::get('testes/criar', [TesteController::class, 'create'])->name('testes.create');
    Route::get('testes/issue-lookup', [TesteController::class, 'lookupIssue'])->name('testes.issue-lookup');
    Route::post('testes', [TesteController::class, 'store'])->name('testes.store');
    Route::get('testes/{teste}', [TesteController::class, 'show'])->name('testes.show');
    Route::delete('testes/{teste}', [TesteController::class, 'destroy'])->name('testes.destroy');

    Route::post('testes/{teste}/cenarios', [CenarioController::class, 'store'])->name('testes.cenarios.store');
    Route::patch('testes/{teste}/cenarios/{cenario}', [CenarioController::class, 'update'])->name('testes.cenarios.update')->scopeBindings();
    Route::delete('testes/{teste}/cenarios/{cenario}', [CenarioController::class, 'destroy'])->name('testes.cenarios.destroy')->scopeBindings();
});
