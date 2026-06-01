<?php

namespace App\Services;

use Google_Client;
use Google_Http_MediaFileUpload;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Google_Service_Exception;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class GoogleDriveService
{
    /**
     * @var mixed
     */
    protected $client;

    /**
     * @var mixed
     */
    protected $service;

    protected bool $usingOauth = false;

    public function __construct()
    {
        $oauthTokenJson = Setting::getValue('google_drive_oauth_tokens');
        $serviceAccountPath = storage_path('app/google-service-account.json');

        if ($oauthTokenJson) {
            $this->usingOauth = true;
            $tokenData = json_decode(decrypt($oauthTokenJson), true);
            $this->client = new \Google_Client();
            $this->client->setClientId(config('services.google.client_id'));
            $this->client->setClientSecret(config('services.google.client_secret'));
            $this->client->setRedirectUri(route('admin.google.drive.callback'));
            $this->client->setAccessType('offline');
            $this->client->setPrompt('consent');
            $this->client->addScope(\Google_Service_Drive::DRIVE);
            $this->client->setAccessToken($tokenData);

            if ($this->client->isAccessTokenExpired()) {
                if (empty($tokenData['refresh_token'])) {
                    throw new \RuntimeException('Google Drive OAuth token has expired and no refresh token is available. Please reconnect Google Drive.');
                }

                $refreshed = $this->client->fetchAccessTokenWithRefreshToken($tokenData['refresh_token']);
                $mergedToken = array_merge($tokenData, $refreshed);

                if (! empty($tokenData['refresh_token']) && empty($mergedToken['refresh_token'])) {
                    $mergedToken['refresh_token'] = $tokenData['refresh_token'];
                }

                $this->persistOauthToken($mergedToken);
                $this->client->setAccessToken($mergedToken);
            }

            $this->service = new \Google_Service_Drive($this->client);

            return;
        }

        if (! file_exists($serviceAccountPath)) {
            throw new \RuntimeException('No Google Drive credentials configured. Please connect Google Drive via admin settings or upload a service account JSON.');
        }

        $this->client = new \Google_Client();
        $this->client->setAuthConfig($serviceAccountPath);
        $this->client->addScope(\Google_Service_Drive::DRIVE);

        $this->service = new \Google_Service_Drive($this->client);
    }

    protected function persistOauthToken(array $token): void
    {
        if (empty($token['created'])) {
            $token['created'] = time();
        }

        Setting::setValue('google_drive_oauth_tokens', encrypt(json_encode($token)), 'google', 'Google Drive OAuth token data');
    }

    public function uploadFile(string $path, string $originalLink): string
    {
        $file = new Google_Service_Drive_DriveFile();
        $file->setName(basename($path));
        $file->setDescription('Uploaded from GetLink process for ' . $originalLink);

        $folderId = env('GOOGLE_DRIVE_FOLDER_ID');

        if ($folderId) {
            $file->setParents([$folderId]);
        } elseif (! $this->usingOauth) {
            throw new \RuntimeException('GOOGLE_DRIVE_FOLDER_ID is required for service account uploads. Use a Shared Drive folder ID.');
        }

        $mimeType = mime_content_type($path) ?: 'application/octet-stream';
        $chunkSizeBytes = 1 * 1024 * 1024; // 1MB chunks

        $this->client->setDefer(true);

        try {
            $request = $this->service->files->create($file, [
                'mimeType' => $mimeType,
                'supportsAllDrives' => true,
                'supportsTeamDrives' => true,
                'uploadType' => 'resumable',
                'fields' => 'id',
            ]);
        } catch (Google_Service_Exception $exception) {
            $message = $exception->getMessage();

            if (str_contains($message, 'storageQuotaExceeded')) {
                throw new \RuntimeException('Google Drive service account has no storage quota. Use a Shared Drive folder or OAuth credentials. ' . $message);
            }

            throw $exception;
        }

        $media = new Google_Http_MediaFileUpload(
            $this->client,
            $request,
            $mimeType,
            null,
            true,
            $chunkSizeBytes
        );
        $media->setFileSize(filesize($path));

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            $this->client->setDefer(false);
            throw new \RuntimeException('Unable to open file for upload: ' . $path);
        }

        $status = false;
        while (! $status && ! feof($handle)) {
            $chunk = fread($handle, $chunkSizeBytes);
            $status = $media->nextChunk($chunk);
        }

        fclose($handle);
        if (function_exists('gc_collect_cycles')) {
            @gc_collect_cycles();
        }
        $this->client->setDefer(false);

        if (! $status || ! isset($status->id)) {
            throw new \RuntimeException('Google Drive upload failed for file: ' . $path);
        }

        return $status->id;
    }

        public function getViewerLink(string $fileId, string $email = null): array
    {
        // Nếu không truyền email vào, mặc định file sẽ private (chỉ trả về link không share public)
        if ($email) {
            $permission = new \Google_Service_Drive_Permission();
            $permission->setType('user');
            $permission->setRole('reader');
            $permission->setEmailAddress($email);

            try {
                $createdPermission = $this->service->permissions->create($fileId, $permission, [
                    'supportsAllDrives' => true,
                    'supportsTeamDrives' => true,
                    'sendNotificationEmail' => false // Không gửi email rác làm phiền user
                ]);
                
                return [
                    'link' => sprintf('https://drive.google.com/file/d/%s/view?usp=sharing', $fileId),
                    'permission_id' => $createdPermission->id
                ];
            } catch (\Throwable $exception) {
                Log::warning('Google Drive permission creation failed', [
                    'file_id' => $fileId,
                    'email' => $email,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return [
            'link' => sprintf('https://drive.google.com/file/d/%s/view?usp=sharing', $fileId),
            'permission_id' => null
        ];
    }

    public function revokePermission(string $fileId, string $permissionId): void
    {
        try {
            $this->service->permissions->delete($fileId, $permissionId, [
                'supportsAllDrives' => true,
                'supportsTeamDrives' => true,
            ]);
            Log::info("Revoked Google Drive permission", ['fileId' => $fileId, 'permissionId' => $permissionId]);
        } catch (\Throwable $exception) {
            Log::warning('Google Drive permission revocation failed', [
                'file_id' => $fileId,
                'permission_id' => $permissionId,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
