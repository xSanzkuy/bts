<?php

use App\Http\Controllers\BtsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BtsController::class, 'index'])->name('home');
Route::get('/history', [BtsController::class, 'history'])->name('history');
Route::get('/analytics', [BtsController::class, 'analytics'])->name('analytics');
Route::get('/triangulation', [BtsController::class, 'triangulation'])->name('triangulation');

Route::post('/api/search', [BtsController::class, 'search'])->name('api.search');
Route::delete('/api/search/{id}', [BtsController::class, 'destroy'])->name('api.search.destroy');
Route::delete('/api/history/clear', [BtsController::class, 'clearHistory'])->name('api.history.clear');
Route::get('/sector-calculator', [BtsController::class, 'sectorCalculator'])->name('sector.calculator');