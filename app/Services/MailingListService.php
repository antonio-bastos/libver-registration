<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MailingListService
{
    public function subscribeUser(User $user, string $source = 'registration'): bool
    {
        $apiKey = (string) config('services.ethermailer.api_key', '');
        $endpoint = (string) config('services.ethermailer.endpoint', '');

        if ($apiKey === '' || $endpoint === '' || $user->email === null) {
            return false;
        }

        $payload = [
            'email' => $user->email,
            'first_name' => $user->name,
            'last_name' => $user->surname,
            'source' => $source,
            'consent' => true,
        ];

        $listId = config('services.ethermailer.list_id');
        if (is_string($listId) && $listId !== '') {
            $payload['list_id'] = $listId;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($endpoint, $payload);

            if ($response->successful()) {
                return true;
            }

            Log::warning('Ethermailer subscribe failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'email' => $user->email,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Ethermailer subscribe exception', [
                'message' => $e->getMessage(),
                'email' => $user->email,
            ]);
        }

        return false;
    }
}

