<?php

namespace App\Jobs;

use App\Services\GoogleDriveService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RevokeGoogleDrivePermission implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $fileId;
    protected string $permissionId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $fileId, string $permissionId)
    {
        $this->fileId = $fileId;
        $this->permissionId = $permissionId;
    }

    /**
     * Execute the job.
     */
    public function handle(GoogleDriveService $driveService): void
    {
        $driveService->revokePermission($this->fileId, $this->permissionId);
    }
}
