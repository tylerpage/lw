<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovedSource extends Model
{
    protected $fillable = [
        'title',
        'source_type',
        'content',
        'file_path',
        'approval_status',
        'approved_by',
        'approved_at',
        'applicable_areas',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'applicable_areas' => 'array',
            'approved_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isActive(): bool
    {
        if ($this->approval_status !== 'approved') {
            return false;
        }

        return ! $this->expires_at || $this->expires_at->isFuture();
    }
}
