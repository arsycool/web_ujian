<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\HistoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

// ---- Auth (guest only) ----
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('/guest-login', [AuthController::class, 'guest'])->name('login.guest');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ---- Aplikasi (perlu login) ----
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/reset-all', [DashboardController::class, 'resetAll'])->name('dashboard.reset-all');

    Route::get('/exam/{session}', [ExamController::class, 'show'])->name('exam.show');
    Route::post('/exam/{session}/answer', [ExamController::class, 'answer'])->name('exam.answer');
    Route::post('/exam/{session}/progress', [ExamController::class, 'progress'])->name('exam.progress');
    Route::post('/exam/{session}/finish', [ExamController::class, 'finish'])->name('exam.finish');
    Route::post('/exam/{session}/reset', [ExamController::class, 'reset'])->name('exam.reset');

    Route::get('/history', [HistoryController::class, 'index'])->name('history');
    Route::get('/history/export-csv', [HistoryController::class, 'exportCsv'])->name('history.export-csv');
});
