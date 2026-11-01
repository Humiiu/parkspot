<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected string $baseUrl;
    protected ?string $secret;

    public function __construct()
    {
        $url = config('firebase.database_url', 'https://parkspot-f925f-default-rtdb.firebaseio.com/');
        $this->baseUrl = rtrim($url, '/') . '/';
        $this->secret = config('firebase.database_secret');
    }

    /**
     * Build the full URL with .json suffix and optional auth query parameter.
     */
    protected function buildUrl(string $path): string
    {
        $cleanPath = ltrim($path, '/');
        $url = $this->baseUrl . $cleanPath . '.json';

        if (!empty($this->secret)) {
            $url .= '?auth=' . $this->secret;
        }

        return $url;
    }

    /**
     * Retrieve data from Firebase RTDB at given path.
     */
    public function get(string $path): mixed
    {
        try {
            $response = Http::timeout(10)->get($this->buildUrl($path));

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning("Firebase GET failed on {$path}: " . $response->body());
            return null;
        } catch (\Throwable $e) {
            Log::error("Firebase GET exception on {$path}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Set / Overwrite data at given path (PUT).
     */
    public function set(string $path, array $data): bool
    {
        try {
            $response = Http::timeout(10)->put($this->buildUrl($path), $data);
            return $response->successful();
        } catch (\Throwable $e) {
            Log::error("Firebase SET exception on {$path}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update specific fields without overwriting siblings (PATCH).
     */
    public function update(string $path, array $data): bool
    {
        try {
            $response = Http::timeout(10)->patch($this->buildUrl($path), $data);
            return $response->successful();
        } catch (\Throwable $e) {
            Log::error("Firebase UPDATE exception on {$path}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Push new item generating a unique auto-ID key (POST).
     * Returns the generated Firebase key or null on failure.
     */
    public function push(string $path, array $data): ?string
    {
        try {
            $response = Http::timeout(10)->post($this->buildUrl($path), $data);

            if ($response->successful()) {
                $result = $response->json();
                return $result['name'] ?? null;
            }

            return null;
        } catch (\Throwable $e) {
            Log::error("Firebase PUSH exception on {$path}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete node at given path (DELETE).
     */
    public function delete(string $path): bool
    {
        try {
            $response = Http::timeout(10)->delete($this->buildUrl($path));
            return $response->successful();
        } catch (\Throwable $e) {
            Log::error("Firebase DELETE exception on {$path}: " . $e->getMessage());
            return false;
        }
    }
}

