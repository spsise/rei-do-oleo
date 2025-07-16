<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Rei do Óleo API',
        'description' => 'API RESTful para sistema de gestão de óleos automotivos',
        'version' => env('API_VERSION', 'v1.0.0'),
        'status' => 'active',
        'timestamp' => now()->toISOString(),
    ], 200, [
        'Content-Type' => 'application/json',
        'X-API-Version' => env('API_VERSION', 'v1.0.0'),
        'X-API-Status' => 'active',
    ]);
});
