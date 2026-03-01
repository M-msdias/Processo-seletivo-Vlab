<?php

use App\Http\Controllers\ResourceController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SmartAssistController;

Route::post('resources/smart-assist', [SmartAssistController::class, 'generate']);

Route::apiResource('resources', ResourceController::class);

Route::get('health', function () {
    return response()->json([
        'status'    => 'ok',
        'timestamp' => now()->toISOString(),
        'database'  => DB::connection()->getPdo() ? 'connected' : 'error',
    ]);
});
