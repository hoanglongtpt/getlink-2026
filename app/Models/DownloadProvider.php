<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DownloadProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'display_name',
        'xu_cost',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'xu_cost' => 'integer',
    ];

    public static function getCostForSlug(?string $slug): int
    {
        if (! $slug) {
            return 1;
        }

        return static::where('slug', $slug)
            ->value('xu_cost')
            ?? 1;
    }

    public static function findOrCreateBySlug(string $slug, ?string $displayName = null): self
    {
        return static::firstOrCreate(
            ['slug' => $slug],
            [
                'display_name' => $displayName ?: $slug,
                'xu_cost' => 1,
                'is_active' => true,
            ]
        );
    }
}
