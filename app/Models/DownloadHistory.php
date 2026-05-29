<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DownloadHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'resource_id',
        'original_link',
        'direct_download_link',
        'xu_cost',
        'status',
        'provider',
        'item_d_code',
        'getstock_slug',
        'getstock_item_id',
        'getstock_type',
        'is_premium',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }
}
