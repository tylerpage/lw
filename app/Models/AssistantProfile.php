<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssistantProfile extends Model
{
    protected $fillable = [
        'name',
        'version',
        'instructions',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function active(): ?self
    {
        return static::query()->where('is_active', true)->orderByDesc('version')->first();
    }
}
