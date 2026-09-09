<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentProposalOperation extends Model
{
    protected $fillable = [
        'proposal_id',
        'sort_order',
        'operation',
    ];

    protected function casts(): array
    {
        return [
            'operation' => 'array',
        ];
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(ContentProposal::class, 'proposal_id');
    }
}
