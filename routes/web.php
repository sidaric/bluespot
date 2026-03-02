<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// 1. A főoldal (localhost/) egyből a /time-ra dob
Route::get('/', function () {
    return redirect()->route('time.index');
});

// 2. A dashboard-ot is átirányítjuk
Route::get('/dashboard', function () {
    return redirect()->route('time.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Itt a fő munkafelület
    Route::view('/time', 'time.index')->name('time.index');
});

require __DIR__.'/auth.php';