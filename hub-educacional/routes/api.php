<?php

use App\Http\Controllers\ResourceController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::apiResource('resources', ResourceController::class);

Route::get('health', function () {
    return response()->json([
        'status'    => 'ok',
        'timestamp' => now()->toISOString(),
        'database'  => DB::connection()->getPdo() ? 'connected' : 'error',
    ]);
});
