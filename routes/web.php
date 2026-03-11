<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\MidtransCallbackController;
use App\Http\Controllers\ProfileController;
use App\Livewire\BookingHistory;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/lapangan/{id}', [HomeController::class, 'detail'])->name('lapangan.detail');
Route::post('midtrans/callback', [MidtransCallbackController::class, 'callback']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/history-booking', function () {
        return view('history');
    })->name('booking.history');
});

require __DIR__.'/auth.php';
