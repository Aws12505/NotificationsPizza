<?php

use App\Http\Controllers\AnnouncementController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.token.store')->prefix('announcements')->group(function () {
    Route::get('/', [AnnouncementController::class, 'index']);
    Route::post('/', [AnnouncementController::class, 'store']);
    Route::get('/visible', [AnnouncementController::class, 'visible']);
    Route::get('/unseen', [AnnouncementController::class, 'unseen']);
    Route::post('/mark-seen', [AnnouncementController::class, 'markSeen']);
    Route::get('/{announcement}', [AnnouncementController::class, 'show']);
    Route::put('/{announcement}', [AnnouncementController::class, 'update']);
    Route::delete('/{announcement}', [AnnouncementController::class, 'destroy']);
});