<?php

use App\Http\Controllers\Admin\CounterController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\OperatorController;
use Illuminate\Support\Facades\Route;

// Halaman depan -> arahkan ke kios ambil tiket.
Route::get('/', fn () => redirect()->route('kiosk.index'));

/*
|--------------------------------------------------------------------------
| Autentikasi Admin (login sederhana bawaan)
|--------------------------------------------------------------------------
*/
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Kios Ambil Tiket
|--------------------------------------------------------------------------
*/
Route::get('/kiosk', [KioskController::class, 'index'])->name('kiosk.index');
Route::post('/kiosk/take', [KioskController::class, 'take'])->name('kiosk.take');
Route::get('/kiosk/ticket/{ticket}/print', [KioskController::class, 'print'])->name('kiosk.print');

/*
|--------------------------------------------------------------------------
| Layar Display / Monitor
|--------------------------------------------------------------------------
*/
Route::get('/display', [DisplayController::class, 'index'])->name('display.index');
Route::get('/display/state', [DisplayController::class, 'state'])->name('display.state');

/*
|--------------------------------------------------------------------------
| Operator / Loket (butuh login; role operator atau admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:operator'])->group(function () {
    Route::get('/operator', [OperatorController::class, 'index'])->name('operator.index');
    Route::post('/operator/{counter}/claim', [OperatorController::class, 'claim'])->name('operator.claim');
    Route::post('/operator/{counter}/release', [OperatorController::class, 'release'])->name('operator.release');
    Route::post('/operator/{counter}/heartbeat', [OperatorController::class, 'heartbeat'])->name('operator.heartbeat');
    Route::get('/operator/{counter}', [OperatorController::class, 'show'])->name('operator.show');
    Route::get('/operator/{counter}/state', [OperatorController::class, 'state'])->name('operator.state');
    Route::post('/operator/{counter}/call-next', [OperatorController::class, 'callNext'])->name('operator.callNext');
    Route::post('/operator/{counter}/recall', [OperatorController::class, 'recall'])->name('operator.recall');
    Route::post('/operator/{counter}/finish', [OperatorController::class, 'finish'])->name('operator.finish');
    Route::post('/operator/{counter}/skip', [OperatorController::class, 'skip'])->name('operator.skip');
});

/*
|--------------------------------------------------------------------------
| Panel Admin
|--------------------------------------------------------------------------
| Untuk contoh ini dibungkus middleware 'auth'. Jika belum pasang autentikasi,
| Anda bisa hapus ->middleware('auth') sementara saat pengembangan.
*/
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', fn () => redirect()->route('admin.counters.index'));

    Route::get('/counters', [CounterController::class, 'index'])->name('admin.counters.index');
    Route::post('/counters', [CounterController::class, 'store'])->name('admin.counters.store');
    Route::put('/counters/{counter}', [CounterController::class, 'update'])->name('admin.counters.update');
    Route::delete('/counters/{counter}', [CounterController::class, 'destroy'])->name('admin.counters.destroy');

    Route::get('/services', [ServiceController::class, 'index'])->name('admin.services.index');
    Route::post('/services', [ServiceController::class, 'store'])->name('admin.services.store');
    Route::put('/services/{service}', [ServiceController::class, 'update'])->name('admin.services.update');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('admin.services.destroy');

    // Laporan antrian + ekspor CSV
    Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports.index');
    Route::get('/reports/export', [ReportController::class, 'exportCsv'])->name('admin.reports.export');
});
