<?php

namespace App\Modules\Core\Services;

use Kreait\Firebase\Factory;
use Google\Cloud\Core\ServiceBuilder;

class FirestoreService
{
    protected $firestore;
    protected $database;

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

            // Validate required fields
            if (empty($credentials['project_id']) || empty($credentials['private_key'])) {
                throw new \Exception("Missing required Firebase credentials in environment variables");
            }

            $factory = (new Factory)
                ->withServiceAccount($credentials)
                ->withProjectId(env('FIREBASE_PROJECT_ID'));

            $this->firestore = $factory->createFirestore();
            $this->database = $this->firestore->database();
            
        } catch (\Exception $e) {
            throw new \Exception("Firebase initialization failed: " . $e->getMessage());
        }
    }

    public function collection(string $name)
    {
        return $this->database->collection($name);
    }

    public function document(string $path)
    {
        return $this->database->document($path);
    }

    public function create(string $collection, array $data, string $id = null)
    {
        try {
            $data['created_at'] = new \DateTime();
            $data['updated_at'] = new \DateTime();
            
            $collectionRef = $this->collection($collection);
            
            if ($id) {
                $docRef = $collectionRef->document($id);
                $docRef->set($data);
                return $id;
            } else {
                $docRef = $collectionRef->add($data);
                return $docRef->id();
            }
        } catch (\Exception $e) {
            throw new \Exception("Error creating document: " . $e->getMessage());
        }
    }

    public function get(string $collection, string $id)
    {
        try {
            $document = $this->collection($collection)->document($id)->snapshot();
            
            if ($document->exists()) {
                return array_merge(['id' => $document->id()], $document->data());
            }
            
            return null;
        } catch (\Exception $e) {
            throw new \Exception("Error retrieving document: " . $e->getMessage());
        }
    }

    public function getAll(string $collection, array $filters = [])
    {
        try {
            $query = $this->collection($collection);
            
            foreach ($filters as $filter) {
                if (count($filter) === 3) {
                    $query = $query->where($filter[0], $filter[1], $filter[2]);
                }
            }
            
            $documents = $query->documents();
            $results = [];
            
            foreach ($documents as $document) {
                if ($document->exists()) {
                    $results[] = array_merge(['id' => $document->id()], $document->data());
                }
            }
            
            return $results;
        } catch (\Exception $e) {
            throw new \Exception("Error retrieving documents: " . $e->getMessage());
        }
    }

    public function update(string $collection, string $id, array $data)
    {
        try {
            $data['updated_at'] = new \DateTime();
            return $this->collection($collection)->document($id)->update($data);
        } catch (\Exception $e) {
            throw new \Exception("Error updating document: " . $e->getMessage());
        }
    }

    public function delete(string $collection, string $id)
    {
        try {
            return $this->collection($collection)->document($id)->delete();
        } catch (\Exception $e) {
            throw new \Exception("Error deleting document: " . $e->getMessage());
        }
    }

    public function batch()
    {
        return $this->database->batch();
    }
}