<?php

namespace App\Models;

use App\Enums\AiConversationStatus;
use App\Enums\ContentTargetType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AiConversation extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'target_type',
        'target_id',
        'status',
        'last_activity_at',
    ];

    protected function casts(): array
    {
        return [
            'target_type' => ContentTargetType::class,
            'status' => AiConversationStatus::class,
            'last_activity_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AiMessage::class, 'conversation_id')->orderBy('created_at');
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(ContentProposal::class, 'conversation_id')->latest();
    }

    public function latestProposal(): HasOne
    {
        return $this->hasOne(ContentProposal::class, 'conversation_id')->latestOfMany();
    }
}
