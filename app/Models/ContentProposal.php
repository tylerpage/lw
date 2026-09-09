<?php

namespace App\Models;

use App\ContentAssistant\Support\ContentRevisionTracker;
use App\ContentAssistant\Support\ContentTargetResolver;
use App\Enums\ContentProposalStatus;
use App\Enums\ContentTargetType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentProposal extends Model
{
    protected $fillable = [
        'conversation_id',
        'user_id',
        'target_type',
        'target_id',
        'expected_revision',
        'content_hash',
        'status',
        'summary',
        'payload',
        'validation_errors',
        'sources',
        'warnings',
        'unverified_claims',
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'target_type' => ContentTargetType::class,
            'status' => ContentProposalStatus::class,
            'payload' => 'array',
            'validation_errors' => 'array',
            'sources' => 'array',
            'warnings' => 'array',
            'unverified_claims' => 'array',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'conversation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function operations(): HasMany
    {
        return $this->hasMany(ContentProposalOperation::class, 'proposal_id')->orderBy('sort_order');
    }

    public function target(): ?Model
    {
        return ContentTargetResolver::find($this->target_type, $this->target_id);
    }

    public function isStale(): bool
    {
        $target = $this->target();

        if (! $target) {
            return true;
        }

        return ContentRevisionTracker::hash($target) !== $this->content_hash;
    }
}
