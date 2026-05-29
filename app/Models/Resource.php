<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_link',
        'provider',
        'is_premium',
        'file_name',
        'file_ext',
        'file_size_bytes',
        'google_drive_link',
        'google_drive_file_id',
        'download_count',
        'status',
        'external_metadata',
    ];

    protected $casts = [
        'is_premium' => 'boolean',
        'external_metadata' => 'array',
    ];

    public function downloadHistories()
    {
        return $this->hasMany(DownloadHistory::class);
    }
}
