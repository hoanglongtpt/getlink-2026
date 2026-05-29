<?php

namespace App\Services;

use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;

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
        $this->client = new \Google_Client();
        $this->client->setAuthConfig(storage_path('app/google-service-account.json'));
        $this->client->addScope(\Google_Service_Drive::DRIVE);

        $this->service = new \Google_Service_Drive($this->client);
    }

    public function uploadFile(string $path, string $originalLink): string
    {
        $file = new Google_Service_Drive_DriveFile();
        $file->setName(basename($path));
        $file->setDescription('Uploaded from GetLink process for ' . $originalLink);

        $result = $this->service->files->create($file, [
            'data' => file_get_contents($path),
            'mimeType' => mime_content_type($path),
            'uploadType' => 'multipart',
        ]);

        return $result->id;
    }

    public function getViewerLink(string $fileId): string
    {
        $permission = new \Google_Service_Drive_Permission();
        $permission->setType('anyone');
        $permission->setRole('reader');
        $this->service->permissions->create($fileId, $permission);

        return sprintf('https://drive.google.com/file/d/%s/view?usp=sharing', $fileId);
    }
}
