<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'description',
    ];

    public static function getValue(string $key, $default = null)
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function setValue(string $key, $value, ?string $group = null, ?string $description = null): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value, 'group' => $group, 'description' => $description]
        );
    }
}
