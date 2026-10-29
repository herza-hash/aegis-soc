<?php

use App\Http\Controllers\ContainmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\LogExplorerController;
use App\Http\Controllers\LogSourceController;
use App\Http\Controllers\LogUploadController;
use App\Http\Controllers\SecurityAlertController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// Log File Upload & Batch Analysis
Route::get('/upload', [LogUploadController::class, 'index'])->name('upload.index');
Route::post('/upload', [LogUploadController::class, 'upload'])->name('upload.process');

// Active Defense & SOAR Containment
Route::get('/containment', [ContainmentController::class, 'index'])->name('containment.index');
Route::post('/containment/block', [ContainmentController::class, 'store'])->name('containment.block');
Route::delete('/containment/{blockedIp}', [ContainmentController::class, 'unblock'])->name('containment.unblock');

// Log Explorer
Route::get('/logs/export', [LogExplorerController::class, 'export'])->name('logs.export');
Route::get('/logs', [LogExplorerController::class, 'index'])->name('logs.index');

// Security Alerts
Route::get('/alerts/{alert}/report', [SecurityAlertController::class, 'report'])->name('alerts.report');
Route::get('/alerts', [SecurityAlertController::class, 'index'])->name('alerts.index');
Route::patch('/alerts/{alert}/status', [SecurityAlertController::class, 'updateStatus'])->name('alerts.update-status');

// Log Sources & Ingestion Simulator
Route::get('/sources', [LogSourceController::class, 'index'])->name('sources.index');
Route::post('/sources', [LogSourceController::class, 'store'])->name('sources.store');
Route::patch('/sources/{source}/toggle', [LogSourceController::class, 'toggleStatus'])->name('sources.toggle');
Route::delete('/sources/{source}', [LogSourceController::class, 'destroy'])->name('sources.destroy');
Route::post('/sources/simulate', [LogSourceController::class, 'simulate'])->name('sources.simulate');
Route::post('/api/logs/ingest', [LogSourceController::class, 'ingestApi'])->name('api.logs.ingest')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);

// System Settings & Detection Rules
Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
Route::post('/settings/rules', [SettingController::class, 'storeRule'])->name('settings.rules.store');
Route::delete('/settings/rules/{rule}', [SettingController::class, 'destroyRule'])->name('settings.rules.destroy');