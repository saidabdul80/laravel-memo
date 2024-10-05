<?php

use Illuminate\Support\Facades\Route;
use Saidabdulsalam\LaravelMemo\Http\Controllers\MemoController;

Route::prefix('api/memos')->group(function () {
    Route::get('/', [MemoController::class, 'index']);
    Route::post('/create-or-update/{id?}', [MemoController::class, 'createOrUpdateMemo']);
    Route::get('/statuses', [MemoController::class, 'memoStatus']);
    Route::get('/types', [MemoController::class, 'memoTypes']);
    Route::get('/members', [MemoController::class, 'members']);
});
