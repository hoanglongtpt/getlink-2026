<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GetstockService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://getstocks.net/api/',
            'timeout' => 30,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function getToken(): string
    {
        if (filled(env('GETSTOCK_ACCESS_TOKEN'))) {
            return env('GETSTOCK_ACCESS_TOKEN');
        }

        return Cache::remember('getstock_access_token', 3600, function () {
            return $this->authenticate();
        });
    }

    protected function authenticate(): string
    {
        $email = env('GETSTOCK_EMAIL');
        $password = env('GETSTOCK_PASSWORD');

        if (! $email || ! $password) {
            throw new \RuntimeException('Getstock API credentials are not configured.');
        }

        $response = $this->client->post('auth/login', [
            'json' => [
                'email' => $email,
                'password' => $password,
            ],
        ]);

        $payload = json_decode((string) $response->getBody(), true);

        if (! isset($payload['result']['access_token'])) {
            Log::error('Getstock authentication failed', ['payload' => $payload]);
            throw new \RuntimeException('Failed to authenticate with Getstock API.');
        }

        return $payload['result']['access_token'];
    }

    protected function authenticatedOptions(): array
    {
        return [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getToken(),
                'Accept' => 'application/json',
            ],
        ];
    }

    public function getInfo(string $link, int $isPre): array
    {
        $response = $this->client->post('v1/getinfo', array_merge($this->authenticatedOptions(), [
            'json' => [
                'link' => $link,
                'ispre' => $isPre,
            ],
        ]));

        return json_decode((string) $response->getBody(), true);
    }

    public function getLink(string $link, int $isPre, ?string $type = null, ?string $webhook = null): array
    {
        $payload = [
            'link' => $link,
            'ispre' => $isPre,
        ];

        if ($type) {
            $payload['type'] = $type;
        }

        if ($webhook) {
            $payload['webhook'] = $webhook;
        }

        $response = $this->client->post('v1/getlink', array_merge($this->authenticatedOptions(), [
            'json' => $payload,
        ]));

        return json_decode((string) $response->getBody(), true);
    }

    public function checkDownloadStatus(string $slug, string $id, int $isPre, string $type): array
    {
        $response = $this->client->post('v1/download-status', array_merge($this->authenticatedOptions(), [
            'json' => [
                'slug' => $slug,
                'id' => $id,
                'ispre' => $isPre,
                'type' => $type,
            ],
        ]));

        return json_decode((string) $response->getBody(), true);
    }

    public function buildDirectDownloadLink(string $itemDCode): string
    {
        return sprintf('https://getstocks.net/api/v1/download/%s?token=%s', $itemDCode, $this->getToken());
    }
}
