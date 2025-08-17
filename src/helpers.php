
<?php

use Illuminate\Support\Facades\Route;
use Saidabdulsalam\LaravelMemo\Http\Controllers\MemoController;

if (!function_exists('memoRoutes')) {
    function memoRoutes()
    {
        Route::prefix('memo')->group(function () {
            Route::get('/boot', [MemoController::class, 'boot']);
            Route::get('/all', [MemoController::class, 'index']);
            Route::post('/', [MemoController::class, 'createOrUpdateMemo']);
            Route::get('/statuses', [MemoController::class, 'memoStatus']);
            Route::get('/types', [MemoController::class, 'memoTypes']);
            Route::get('/members', [MemoController::class, 'members']);
            Route::post('/reject', [MemoController::class, 'rejectMemo']);
            Route::post('/approve', [MemoController::class, 'approveMemo']);
            Route::post('/make_comment', [MemoController::class, 'saveComment']);
            Route::put('/comment/{id}', [MemoController::class, 'updateComment']);
            Route::delete('/comment/{id}', [MemoController::class, 'deleteComment']);
            Route::get('/departments', [MemoController::class, 'departments']);
            Route::delete('/{id}', [MemoController::class, 'deleteMemo']);
        });
    }
}
