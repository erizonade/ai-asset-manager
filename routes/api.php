<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PromptController;
use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\AutomationController;
use App\Http\Controllers\Api\ExportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
|
*/

// Categories
Route::apiResource('categories', CategoryController::class);

// Prompts
Route::get('/prompts/categories', [PromptController::class, 'categories']);
Route::post('/prompts/generate', [PromptController::class, 'generate']);
Route::post('/prompts/generate-batch', [PromptController::class, 'generateBatch']);
Route::apiResource('prompts', PromptController::class);

// Assets
Route::post('/assets/generate/image', [AssetController::class, 'generateImage']);
Route::post('/assets/generate/batch', [AssetController::class, 'batchGenerate']);
Route::post('/assets/generate/video', [AssetController::class, 'generateVideo']);
Route::post('/assets/upload', [AssetController::class, 'upload']);
Route::post('/assets/{asset}/optimize', [AssetController::class, 'optimize']);
Route::get('/assets/{asset}/metadata', [AssetController::class, 'metadata']);
Route::put('/assets/{asset}/status', [AssetController::class, 'updateStatus']);
Route::apiResource('assets', AssetController::class);

// Stats
Route::get('/stats', [AssetController::class, 'stats']);

// Automation
Route::post('/automation/schedules/{schedule}/run', [AutomationController::class, 'run']);
Route::post('/automation/schedules/{schedule}/toggle', [AutomationController::class, 'toggle']);
Route::post('/automation/run-all', [AutomationController::class, 'runAll']);
Route::apiResource('automation/schedules', AutomationController::class);

// Export
Route::get('/export/preview', [ExportController::class, 'preview']);
Route::get('/export/csv', [ExportController::class, 'exportCsv']);
Route::get('/export/json', [ExportController::class, 'exportJson']);
Route::get('/export/zip', [ExportController::class, 'exportStructured']);