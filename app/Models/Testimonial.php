<?php

namespace App\Models;

use App\Enums\PublishStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = [
        'quote', 'attribution', 'role', 'company', 'featured', 'sort_order', 'status',
    ];

    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
            'status' => PublishStatus::class,
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PublishStatus::Published);
    }
}
