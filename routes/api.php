<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TimeEntryController;

Route::middleware('auth')->group(function () {
    Route::get('/time-entries', [TimeEntryController::class, 'index']);
    Route::post('/time-entries', [TimeEntryController::class, 'store']);
    Route::put('/time-entries/{timeEntry}', [TimeEntryController::class, 'update']);
    Route::delete('/time-entries/{timeEntry}', [TimeEntryController::class, 'destroy']);
});
