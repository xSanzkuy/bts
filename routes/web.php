<?php

use App\Http\Controllers\BtsController;
use App\Http\Controllers\TokenController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BtsController::class, 'index'])->name('home');
Route::get('/history', [BtsController::class, 'history'])->name('history');
Route::get('/analytics', [BtsController::class, 'analytics'])->name('analytics');
Route::get('/triangulation', [BtsController::class, 'triangulation'])->name('triangulation');
Route::get('/sector-calculator', [BtsController::class, 'sectorCalculator'])->name('sector.calculator');

// Token Management Routes - BARU
Route::get('/tokens', [TokenController::class, 'index'])->name('tokens');
Route::get('/api/tokens/active', [TokenController::class, 'getActiveTokens'])->name('tokens.active');
Route::post('/api/tokens', [TokenController::class, 'store'])->name('tokens.store');
Route::post('/api/tokens/import', [TokenController::class, 'importCsv'])->name('tokens.import');
Route::put('/api/tokens/{id}', [TokenController::class, 'update'])->name('tokens.update');
Route::delete('/api/tokens/{id}', [TokenController::class, 'destroy'])->name('tokens.destroy');
Route::post('/api/tokens/{id}/reset-usage', [TokenController::class, 'resetUsage'])->name('tokens.reset');

// BTS Search Routes
Route::post('/api/search', [BtsController::class, 'search'])->name('api.search');
Route::delete('/api/search/{id}', [BtsController::class, 'destroy'])->name('api.search.destroy');
Route::delete('/api/history/clear', [BtsController::class, 'clearHistory'])->name('api.history.clear');