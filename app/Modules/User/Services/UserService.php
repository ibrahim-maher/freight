<?php

namespace App\Modules\User\Services;

use App\Modules\Core\Services\FirestoreService;

class UserService
{
    protected $firestoreService;

    public function __construct(FirestoreService $firestoreService)
    {
        $this->firestoreService = $firestoreService;
    }

    public function getAllUsers(array $filters = [])
    {
        return $this->firestoreService->getAll('users', $filters);
    }

    public function getUserById(string $id)
    {
        return $this->firestoreService->get('users', $id);
    }

    public function createUser(array $data)
    {
        return $this->firestoreService->create('users', $data);
    }

    public function updateUser(string $id, array $data)
    {
        return $this->firestoreService->update('users', $id, $data);
    }

    public function deleteUser(string $id)
    {
        return $this->firestoreService->delete('users', $id);
    }

    public function searchUsers(string $searchTerm)
    {
        // Search by name or email
        $nameResults = $this->firestoreService->getAll('users', [
            ['name', '>=', $searchTerm],
            ['name', '<=', $searchTerm . '\uf8ff']
        ]);
        
        $emailResults = $this->firestoreService->getAll('users', [
            ['email', '>=', $searchTerm],
            ['email', '<=', $searchTerm . '\uf8ff']
        ]);
        
        // Merge and remove duplicates
        $allResults = array_merge($nameResults, $emailResults);
        $uniqueResults = array_values(array_unique($allResults, SORT_REGULAR));
        
        return $uniqueResults;
    }

    public function getUsersByRole(string $role)
    {
        return $this->firestoreService->getAll('users', [['role', '==', $role]]);
    }

    public function updateUserStatus(string $id, string $status)
    {
        return $this->firestoreService->update('users', $id, [
            'status' => $status,
            'status_updated_at' => new \DateTime()
        ]);
    }
}