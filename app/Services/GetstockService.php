<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
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

    protected ?string $token = null;
    protected bool $useEnvToken = true;

    public function getToken(): string
    {
        if ($this->token !== null) {
            return $this->token;
        }

        if ($this->useEnvToken && filled(env('GETSTOCK_ACCESS_TOKEN'))) {
            $this->token = env('GETSTOCK_ACCESS_TOKEN');
            return $this->token;
        }

        return $this->token = Cache::remember('getstock_access_token', 3600, function () {
            return $this->authenticate();
        });
    }

    protected function performRequest(string $method, string $uri, array $options = [], bool $retry = true)
    {
        $authenticatedOptions = $this->authenticatedOptions();
        $options['headers'] = array_merge($authenticatedOptions['headers'], $options['headers'] ?? []);
        $options['query'] = array_merge($options['query'] ?? [], $authenticatedOptions['query']);

        try {
            return $this->client->request($method, $uri, $options);
        } catch (ClientException $exception) {
            $response = $exception->getResponse();

            if (
                $retry &&
                $response &&
                $response->getStatusCode() === 401
            ) {
                Log::warning('Getstock token expired or invalid, refreshing token', [
                    'uri' => $uri,
                    'message' => $exception->getMessage(),
                ]);

                $this->token = null;
                $this->useEnvToken = false;
                Cache::forget('getstock_access_token');

                return $this->performRequest($method, $uri, $options, false);
            }

            throw $exception;
        }
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
        $token = $this->getToken();

        return [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ],
            'query' => [
                'token' => $token,
            ],
        ];
    }

    public function firstSupportedType(array $info): ?string
    {
        $types = data_get($info, 'result.support.type');

        if (! is_array($types) || $types === []) {
            return data_get($info, 'result.itemType');
        }

        $firstKey = array_key_first($types);
        $firstValue = $types[$firstKey] ?? null;

        if (is_string($firstKey) && $firstKey !== '') {
            return $firstKey;
        }

        return is_string($firstValue) && $firstValue !== '' ? $firstValue : null;
    }

    public function getInfo(string $link, int $isPre): array
    {
        $response = $this->performRequest('POST', 'v1/getinfo', [
            'json' => [
                'link' => $link,
                'ispre' => $isPre,
            ],
        ]);

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

        $response = $this->performRequest('POST', 'v1/getlink', [
            'json' => $payload,
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    public function checkDownloadStatus(string $slug, string $id, int $isPre, string $type): array
    {
        $response = $this->performRequest('POST', 'v1/download-status', [
            'json' => [
                'slug' => $slug,
                'id' => $id,
                'ispre' => $isPre,
                'type' => $type,
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    public function buildDirectDownloadLink(string $itemDCode): string
    {
        return sprintf('https://getstocks.net/api/v1/download/%s?token=%s', $itemDCode, $this->getToken());
    }
}
