<?php

namespace App\Enums;

enum ContentProposalStatus: string
{
    case CollectingContext = 'collecting_context';
    case Proposed = 'proposed';
    case NeedsRevision = 'needs_revision';
    case Validated = 'validated';
    case DraftSaved = 'draft_saved';
    case AwaitingApproval = 'awaiting_approval';
    case Approved = 'approved';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case Rejected = 'rejected';
    case Failed = 'failed';
    case RolledBack = 'rolled_back';
    case Stale = 'stale';

    public function label(): string
    {
        return str($this->value)->replace('_', ' ')->title()->toString();
    }
}
