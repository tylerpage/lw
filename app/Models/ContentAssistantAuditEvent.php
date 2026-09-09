<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentAssistantAuditEvent extends Model
{
    protected $fillable = [
        'user_id',
        'event_type',
        'subject_type',
        'subject_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $eventType, ?User $user = null, ?Model $subject = null, array $metadata = []): self
    {
        return static::query()->create([
            'user_id' => $user?->id,
            'event_type' => $eventType,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'metadata' => $metadata,
        ]);
    }
}
