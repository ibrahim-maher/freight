<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Auth\Services\FirebaseAuthService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $authService;
    protected $firebaseAuthService;

    public function __construct(AuthService $authService, FirebaseAuthService $firebaseAuthService)
    {
        $this->authService = $authService;
        $this->firebaseAuthService = $firebaseAuthService;
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'name' => 'required|string|max:255|min:2',
                'email' => 'required|email|max:255',
                'password' => 'required|string|min:6',
                'uid' => 'required|string'
            ]);

            // Check if user already exists in Firestore
            $existingUser = $this->authService->getUserByEmail($validated['email']);
            if (!empty($existingUser)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User with this email already exists in database'
                ], 400);
            }

            // Store user data in Firestore (Firebase Auth already created the user)
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'uid' => $validated['uid'],
                'email_verified' => false,
                'profile' => [
                    'display_name' => $validated['name'],
                    'photo_url' => null,
                ],
                'settings' => [
                    'notifications' => true,
                    'theme' => 'light',
                ],
            ];

            $this->authService->createUserDocument($validated['uid'], $userData);
            
            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'user' => $userData
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verifyToken(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_token' => 'required|string'
            ]);

            $idToken = $validated['id_token'];
            $verifiedToken = $this->firebaseAuthService->verifyIdToken($idToken);
            
            if ($verifiedToken) {
                $uid = $verifiedToken->claims()->get('sub');
                $user = $this->authService->getUser($uid);
                
                return response()->json([
                    'success' => true,
                    'user' => $user,
                    'firebase_user' => [
                        'uid' => $uid,
                        'email' => $verifiedToken->claims()->get('email'),
                        'name' => $verifiedToken->claims()->get('name'),
                        'email_verified' => $verifiedToken->claims()->get('email_verified'),
                    ]
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token'
            ], 401);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token is required',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token verification failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            // Firebase logout is handled on the frontend
            // Here we can perform any server-side cleanup if needed
            
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function profile(Request $request)
    {
        try {
            $uid = $request->attributes->get('firebase_user_id');
            $user = $this->authService->getUser($uid);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get profile: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255|min:2',
                'phone_number' => 'sometimes|string|max:20',
                'photo_url' => 'sometimes|url',
                'settings' => 'sometimes|array',
            ]);

            $uid = $request->attributes->get('firebase_user_id');
            
            $updateData = [];
            if (isset($validated['name'])) {
                $updateData['name'] = $validated['name'];
                $updateData['profile.display_name'] = $validated['name'];
            }
            if (isset($validated['phone_number'])) {
                $updateData['profile.phone_number'] = $validated['phone_number'];
            }
            if (isset($validated['photo_url'])) {
                $updateData['profile.photo_url'] = $validated['photo_url'];
            }
            if (isset($validated['settings'])) {
                foreach ($validated['settings'] as $key => $value) {
                    $updateData["settings.{$key}"] = $value;
                }
            }

            $this->authService->updateUser($uid, $updateData);
            
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Profile update failed: ' . $e->getMessage()
            ], 500);
        }
    }
}