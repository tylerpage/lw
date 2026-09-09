<?php

namespace App\Models;

use App\Enums\AiMessageRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMessage extends Model
{
    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'role' => AiMessageRole::class,
            'metadata' => 'array',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'conversation_id');
    }

    /**
     * @return array<int, array{path: string, url: string, original_name?: string, mime_type?: string, size?: int}>
     */
    public function attachments(): array
    {
        return $this->metadata['attachments'] ?? [];
    }
}
