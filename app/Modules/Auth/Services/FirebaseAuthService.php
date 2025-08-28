<?php

namespace App\Modules\Auth\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;

class FirebaseAuthService
{
    protected $auth;

    public function __construct()
    {
        try {
            // Build credentials from environment variables
            $credentials = [
                'type' => env('FIREBASE_TYPE', 'service_account'),
                'project_id' => env('FIREBASE_PROJECT_ID'),
                'private_key_id' => env('FIREBASE_PRIVATE_KEY_ID'),
                'private_key' => str_replace('\\n', "\n", env('FIREBASE_PRIVATE_KEY')),
                'client_email' => env('FIREBASE_CLIENT_EMAIL'),
                'client_id' => env('FIREBASE_CLIENT_ID'),
                'auth_uri' => env('FIREBASE_AUTH_URI'),
                'token_uri' => env('FIREBASE_TOKEN_URI'),
                'auth_provider_x509_cert_url' => env('FIREBASE_AUTH_PROVIDER_CERT_URL'),
                'client_x509_cert_url' => env('FIREBASE_CLIENT_CERT_URL'),
            ];

            $factory = (new Factory)
                ->withServiceAccount($credentials)
                ->withProjectId(env('FIREBASE_PROJECT_ID'));

            $this->auth = $factory->createAuth();
        } catch (\Exception $e) {
            throw new \Exception("Firebase Auth initialization failed: " . $e->getMessage());
        }
    }

    public function createUser(array $userData)
    {
        return $this->auth->createUser($userData);
    }

    public function verifyIdToken(string $idToken)
    {
        try {
            return $this->auth->verifyIdToken($idToken);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getUser(string $uid)
    {
        return $this->auth->getUser($uid);
    }

    public function updateUser(string $uid, array $properties)
    {
        return $this->auth->updateUser($uid, $properties);
    }

    public function deleteUser(string $uid)
    {
        return $this->auth->deleteUser($uid);
    }

    public function listUsers(int $maxResults = 1000, string $pageToken = null)
    {
        return $this->auth->listUsers($maxResults, $pageToken);
    }
}