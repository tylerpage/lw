<?php

namespace App\Policies;

use App\Models\ContentProposal;
use App\Models\User;

class ContentProposalPolicy
{
    public function view(User $user, ContentProposal $proposal): bool
    {
        return app(AiConversationPolicy::class)->view($user, $proposal->conversation);
    }

    public function validate(User $user, ContentProposal $proposal): bool
    {
        $target = $proposal->target();

        return $target && $user->can('update', $target) && $this->view($user, $proposal);
    }

    public function applyDraft(User $user, ContentProposal $proposal): bool
    {
        return $this->validate($user, $proposal);
    }

    public function approve(User $user, ContentProposal $proposal): bool
    {
        return $user->isSuperAdmin() || $user->isEditor();
    }

    public function publish(User $user, ContentProposal $proposal): bool
    {
        return $this->approve($user, $proposal) && $this->validate($user, $proposal);
    }
}
