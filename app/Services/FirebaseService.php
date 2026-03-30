<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FirebaseService
{
    public function send(string $token, array $payload): void
    {
        Http::withToken(config('services.firebase.server_key'))
            ->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $token,
                'notification' => [
                    'title' => (string) data_get($payload, 'title', ''),
                    'body' => (string) data_get($payload, 'body', ''),
                ],
                'data' => $payload,
            ])
            ->throw();
    }
}