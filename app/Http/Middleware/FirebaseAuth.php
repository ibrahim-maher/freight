<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Modules\Auth\Services\FirebaseAuthService;

class FirebaseAuth
{
    protected $firebaseAuthService;

    public function __construct()
    {
        // Only initialize if Firebase is configured
        if (env('FIREBASE_PROJECT_ID') && env('FIREBASE_PRIVATE_KEY')) {
            $this->firebaseAuthService = app(FirebaseAuthService::class);
        }
    }

    public function handle(Request $request, Closure $next)
    {
        // Check if Firebase is configured
        if (!$this->firebaseAuthService) {
            return response()->json([
                'success' => false,
                'message' => 'Firebase authentication not configured'
            ], 500);
        }

        $token = $request->bearerToken() ?: $request->input('id_token');
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication token required'
            ], 401);
        }
        
        $verifiedToken = $this->firebaseAuthService->verifyIdToken($token);
        
        if (!$verifiedToken) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired authentication token'
            ], 401);
        }
        
        // Add user ID to request for use in controllers
        $request->attributes->set('firebase_user_id', $verifiedToken->claims()->get('sub'));
        $request->attributes->set('firebase_email', $verifiedToken->claims()->get('email'));
        
        return $next($request);
    }
}