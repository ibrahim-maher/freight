<?php

use Illuminate\Support\Facades\Route;

// Test routes (keep for debugging)
Route::post('/test-auth', function(Illuminate\Http\Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'Laravel backend working perfectly!',
        'firebase_configured' => !empty(env('FIREBASE_PROJECT_ID')),
        'timestamp' => now()->toISOString(),
        'request_data' => $request->all(),
        'method' => $request->method(),
        'url' => $request->url(),
    ]);
});

// Environment test route
Route::get('/test-env', function() {
    return response()->json([
        'firebase_configured' => !empty(env('FIREBASE_PROJECT_ID')),
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'has_private_key' => !empty(env('FIREBASE_PRIVATE_KEY')),
        'has_client_email' => !empty(env('FIREBASE_CLIENT_EMAIL')),
        'has_api_key' => !empty(env('FIREBASE_API_KEY')),
        'timestamp' => now()->toISOString(),
    ]);
});

// Root redirect
Route::get('/', function () {
    return redirect('/login');
});