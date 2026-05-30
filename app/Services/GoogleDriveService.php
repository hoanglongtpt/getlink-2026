<?php

namespace App\Services;

use Google_Client;
use Google_Http_MediaFileUpload;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Google_Service_Exception;
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

    public function __construct()
    {
        $serviceAccountPath = storage_path('app/google-service-account.json');

        if (! file_exists($serviceAccountPath)) {
            throw new \RuntimeException('Google service account credential file not found: ' . $serviceAccountPath);
        }

        $this->client = new \Google_Client();
        $this->client->setAuthConfig($serviceAccountPath);
        $this->client->addScope(\Google_Service_Drive::DRIVE);

        $this->service = new \Google_Service_Drive($this->client);
    }

    public function uploadFile(string $path, string $originalLink): string
    {
        $file = new Google_Service_Drive_DriveFile();
        $file->setName(basename($path));
        $file->setDescription('Uploaded from GetLink process for ' . $originalLink);

        $folderId = env('GOOGLE_DRIVE_FOLDER_ID');
        if (! $folderId) {
            throw new \RuntimeException('GOOGLE_DRIVE_FOLDER_ID is required for service account uploads. Use a Shared Drive folder ID.');
        }

        $file->setParents([$folderId]);

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
        $this->client->setDefer(false);

        if (! $status || ! isset($status->id)) {
            throw new \RuntimeException('Google Drive upload failed for file: ' . $path);
        }

        return $status->id;
    }

    public function getViewerLink(string $fileId): string
    {
        $permission = new \Google_Service_Drive_Permission();
        $permission->setType('anyone');
        $permission->setRole('reader');

        try {
            $this->service->permissions->create($fileId, $permission, [
                'supportsAllDrives' => true,
                'supportsTeamDrives' => true,
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Google Drive permission creation failed', [
                'file_id' => $fileId,
                'error' => $exception->getMessage(),
            ]);
        }

        return sprintf('https://drive.google.com/file/d/%s/view?usp=sharing', $fileId);
    }
}
