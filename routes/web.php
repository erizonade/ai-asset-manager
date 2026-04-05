<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\AssetController as WebAssetController;
use App\Http\Controllers\Web\PromptController as WebPromptController;
use App\Http\Controllers\Web\AutomationController as WebAutomationController;
use App\Http\Controllers\Web\ExportController as WebExportController;
use App\Http\Controllers\Web\CategoryController as WebCategoryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Assets
Route::get('/assets', [WebAssetController::class, 'index'])->name('assets.index');
Route::get('/assets/create', [WebAssetController::class, 'create'])->name('assets.create');
Route::post('/assets', [WebAssetController::class, 'store'])->name('assets.store');
Route::post('/assets/video', [WebAssetController::class, 'storeVideo'])->name('assets.video');
Route::post('/assets/batch', [WebAssetController::class, 'storeBatch'])->name('assets.batch');
Route::post('/assets/upload', [WebAssetController::class, 'upload'])->name('assets.upload');
Route::get('/assets/{asset}', [WebAssetController::class, 'show'])->name('assets.show');
Route::post('/assets/{asset}/status', [WebAssetController::class, 'updateStatus'])->name('assets.status');
Route::post('/assets/{asset}/optimize', [WebAssetController::class, 'optimize'])->name('assets.optimize');
Route::post('/assets/{asset}/upscale', [WebAssetController::class, 'upscale'])->name('assets.upscale');
Route::delete('/assets/{asset}', [WebAssetController::class, 'destroy'])->name('assets.destroy');

// Prompts
Route::get('/prompts', [WebPromptController::class, 'index'])->name('prompts.index');
Route::get('/prompts/create', [WebPromptController::class, 'create'])->name('prompts.create');
Route::post('/prompts', [WebPromptController::class, 'store'])->name('prompts.store');
Route::post('/prompts/generate', [WebPromptController::class, 'generate'])->name('prompts.generate');
Route::post('/prompts/generate-batch', [WebPromptController::class, 'generateBatch'])->name('prompts.generateBatch');
Route::get('/prompts/{prompt}', [WebPromptController::class, 'show'])->name('prompts.show');
Route::put('/prompts/{prompt}', [WebPromptController::class, 'update'])->name('prompts.update');
Route::delete('/prompts/{prompt}', [WebPromptController::class, 'destroy'])->name('prompts.destroy');

// Automation
Route::get('/automation', [WebAutomationController::class, 'index'])->name('automation.index');
Route::get('/automation/create', [WebAutomationController::class, 'create'])->name('automation.create');
Route::post('/automation', [WebAutomationController::class, 'store'])->name('automation.store');
Route::post('/automation/{schedule}/run', [WebAutomationController::class, 'run'])->name('automation.run');
Route::post('/automation/{schedule}/toggle', [WebAutomationController::class, 'toggle'])->name('automation.toggle');
Route::delete('/automation/{schedule}', [WebAutomationController::class, 'destroy'])->name('automation.destroy');

// Export
Route::get('/export', [WebExportController::class, 'index'])->name('export.index');
Route::get('/export/csv', [WebExportController::class, 'exportCsv'])->name('export.csv');
Route::get('/export/json', [WebExportController::class, 'exportJson'])->name('export.json');
Route::get('/export/zip', [WebExportController::class, 'exportZip'])->name('export.zip');

// Categories
Route::get('/categories', [WebCategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/create', [WebCategoryController::class, 'create'])->name('categories.create');
Route::post('/categories', [WebCategoryController::class, 'store'])->name('categories.store');
Route::get('/categories/{category}', [WebCategoryController::class, 'show'])->name('categories.show');
Route::get('/categories/{category}/edit', [WebCategoryController::class, 'edit'])->name('categories.edit');
Route::put('/categories/{category}', [WebCategoryController::class, 'update'])->name('categories.update');
Route::delete('/categories/{category}', [WebCategoryController::class, 'destroy'])->name('categories.destroy');