<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MailingListService
{
    public function subscribeUser(User $user, string $source = 'registration'): bool
    {
        $apiKey = (string) config('services.ethermailer.api_key', '');
        $configuredEndpoint = trim((string) config('services.ethermailer.endpoint', ''));
        $fallbackEndpoint = 'https://api.ethermailer.com/user/contact';
        $endpoints = array_values(array_unique(array_filter([$configuredEndpoint, $fallbackEndpoint])));

        if ($configuredEndpoint !== '' && str_contains($configuredEndpoint, '/v1/subscribers')) {
            $endpoints = [$fallbackEndpoint];
        }

        if ($apiKey === '' || $user->email === null || $endpoints === []) {
            return false;
        }

        $groupId = $this->resolveGroupId($endpoints, $apiKey);
        if (!$groupId) {
            Log::warning('Ethermailer subscribe skipped: no group id available', [
                'email' => $user->email,
                'source' => $source,
            ]);
            return false;
        }

        $payload = [
            'email' => $user->email,
            'name' => $user->name,
            'surname' => $user->surname,
            'groups' => [
                ['groupId' => $groupId],
            ],
            'extraFieldValues' => [],
        ];

        foreach ($endpoints as $endpoint) {
            try {
                $response = Http::timeout(12)
                    ->withHeaders($this->authHeaders($apiKey))
                    ->post($endpoint, $payload);

                if ($response->successful()) {
                    Log::info('Ethermailer subscribe success', [
                        'endpoint' => $endpoint,
                        'group_id' => $groupId,
                        'email' => $user->email,
                        'source' => $source,
                    ]);
                    return true;
                }

                $responseBody = (string) $response->body();
                $decodedBody = json_decode($responseBody, true);
                $errors = Arr::get($decodedBody, 'errors', []);
                $alreadyExists = collect(is_array($errors) ? $errors : [])
                    ->contains(function ($error) {
                        $constraint = strtolower((string) Arr::get($error, 'constraintName', ''));
                        return $constraint === 'alreadyexists';
                    });

                if ($alreadyExists) {
                    return true;
                }

                Log::warning('Ethermailer subscribe failed', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'body' => $responseBody,
                    'email' => $user->email,
                    'source' => $source,
                    'group_id' => $groupId,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Ethermailer subscribe exception', [
                    'endpoint' => $endpoint,
                    'message' => $e->getMessage(),
                    'email' => $user->email,
                    'source' => $source,
                    'group_id' => $groupId,
                ]);
            }
        }

        return false;
    }

    private function resolveGroupId(array $endpoints, string $apiKey): ?string
    {
        $configuredListId = trim((string) config('services.ethermailer.list_id', ''));
        if ($configuredListId !== '') {
            return $configuredListId;
        }

        $cacheKey = 'ethermailer.group_id.' . substr(sha1($apiKey), 0, 12);
        $cachedGroupId = Cache::get($cacheKey);
        if (is_string($cachedGroupId) && $cachedGroupId !== '') {
            return $cachedGroupId;
        }

        $headers = $this->authHeaders($apiKey);
        foreach ($endpoints as $endpoint) {
            $baseUrl = $this->baseUrlFromEndpoint($endpoint);
            if ($baseUrl === null) {
                continue;
            }

            $groupId = $this->fetchFirstGroupId($baseUrl, $headers);
            if ($groupId === null) {
                $groupId = $this->createFallbackGroup($baseUrl, $headers);
            }

            if ($groupId !== null) {
                Cache::put($cacheKey, $groupId, now()->addDay());
                return $groupId;
            }
        }

        return null;
    }

    private function fetchFirstGroupId(string $baseUrl, array $headers): ?string
    {
        $url = rtrim($baseUrl, '/') . '/user/contact-groups/limit/1/offset/0';

        try {
            $response = Http::timeout(12)
                ->withHeaders($headers)
                ->get($url);

            if (!$response->successful()) {
                Log::warning('Ethermailer group list failed', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => (string) $response->body(),
                ]);
                return null;
            }

            $items = Arr::get($response->json(), 'items', []);
            if (!is_array($items) || $items === []) {
                return null;
            }

            $groupId = Arr::get($items[0], 'groupId');
            return is_string($groupId) && $groupId !== '' ? $groupId : null;
        } catch (\Throwable $e) {
            Log::warning('Ethermailer group list exception', [
                'url' => $url,
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function createFallbackGroup(string $baseUrl, array $headers): ?string
    {
        $url = rtrim($baseUrl, '/') . '/user/contact-groups';
        $groupName = 'LibVer Subscribers';

        try {
            $response = Http::timeout(12)
                ->withHeaders($headers)
                ->post($url, ['name' => $groupName]);

            if (!$response->successful()) {
                Log::warning('Ethermailer fallback group create failed', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => (string) $response->body(),
                ]);
                return null;
            }

            $groupId = Arr::get($response->json(), 'text');
            return is_string($groupId) && $groupId !== '' ? $groupId : null;
        } catch (\Throwable $e) {
            Log::warning('Ethermailer fallback group create exception', [
                'url' => $url,
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function authHeaders(string $apiKey): array
    {
        return [
            'Authorization' => 'API ' . $apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    private function baseUrlFromEndpoint(string $endpoint): ?string
    {
        $parts = parse_url($endpoint);
        $scheme = $parts['scheme'] ?? null;
        $host = $parts['host'] ?? null;
        if (!$scheme || !$host) {
            return null;
        }

        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        return $scheme . '://' . $host . $port;
    }
}
