<?php

namespace App\Policies;

use App\Models\AiConversation;
use App\Models\User;

class AiConversationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isEditor() || $user->isAuthor();
    }

    public function view(User $user, AiConversation $conversation): bool
    {
        if ($user->isSuperAdmin() || $user->isEditor()) {
            return true;
        }

        return $user->isAuthor() && $conversation->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, AiConversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }
}
