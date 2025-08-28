<?php

namespace App\Modules\Auth\Services;

use App\Modules\Core\Services\FirestoreService;

class AuthService
{
    protected $firestoreService;
    protected $firebaseAuthService;

    public function __construct(FirestoreService $firestoreService, FirebaseAuthService $firebaseAuthService)
    {
        $this->firestoreService = $firestoreService;
        $this->firebaseAuthService = $firebaseAuthService;
    }

    public function createUser(array $userData)
    {
        try {
            // Create user in Firebase Auth
            $firebaseUser = $this->firebaseAuthService->createUser([
                'email' => $userData['email'],
                'password' => $userData['password'],
                'displayName' => $userData['name'],
                'emailVerified' => false,
            ]);

            // Store additional user data in Firestore
            $userDocument = [
                'name' => $userData['name'],
                'email' => $userData['email'],
                'uid' => $firebaseUser->uid,
                'email_verified' => false,
                'created_at' => new \DateTime(),
                'updated_at' => new \DateTime(),
                'profile' => [
                    'display_name' => $userData['name'],
                    'photo_url' => null,
                    'phone_number' => null,
                ],
                'settings' => [
                    'notifications' => true,
                    'theme' => 'light',
                    'language' => 'en',
                ],
            ];

            $this->firestoreService->create('users', $userDocument, $firebaseUser->uid);

            return [
                'firebase_user' => $firebaseUser,
                'user_document' => $userDocument
            ];
        } catch (\Exception $e) {
            throw new \Exception("User creation failed: " . $e->getMessage());
        }
    }

    public function createUserDocument(string $uid, array $userData)
    {
        $userData['created_at'] = new \DateTime();
        $userData['updated_at'] = new \DateTime();
        return $this->firestoreService->create('users', $userData, $uid);
    }

    public function getUser(string $uid)
    {
        return $this->firestoreService->get('users', $uid);
    }

    public function updateUser(string $uid, array $data)
    {
        $data['updated_at'] = new \DateTime();
        return $this->firestoreService->update('users', $uid, $data);
    }

    public function deleteUser(string $uid)
    {
        // Delete from Firestore
        $this->firestoreService->delete('users', $uid);
        
        // Delete from Firebase Auth
        return $this->firebaseAuthService->deleteUser($uid);
    }

    public function getUserByEmail(string $email)
    {
        return $this->firestoreService->getAll('users', [['email', '==', $email]]);
    }

    public function getAllUsers(array $filters = [])
    {
        return $this->firestoreService->getAll('users', $filters);
    }
}