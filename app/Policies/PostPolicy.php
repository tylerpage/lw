<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isEditor() || $user->isAuthor();
    }

    public function view(User $user, Post $post): bool
    {
        if ($user->isSuperAdmin() || $user->isEditor()) {
            return true;
        }

        return $user->isAuthor() && $post->author?->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Post $post): bool
    {
        return $this->view($user, $post);
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->isSuperAdmin() || $user->isEditor();
    }
}
