<?php

namespace App\Policies;

use App\Models\ContactSubmission;
use App\Models\User;

class ContactSubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isEditor();
    }

    public function view(User $user, ContactSubmission $contactSubmission): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, ContactSubmission $contactSubmission): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, ContactSubmission $contactSubmission): bool
    {
        return $user->isSuperAdmin();
    }
}
