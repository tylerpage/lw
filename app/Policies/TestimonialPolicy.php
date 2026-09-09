<?php

namespace App\Policies;

use App\Models\Testimonial;
use App\Models\User;

class TestimonialPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isEditor();
    }

    public function view(User $user, Testimonial $testimonial): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Testimonial $testimonial): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Testimonial $testimonial): bool
    {
        return $user->isSuperAdmin() || $user->isEditor();
    }
}
