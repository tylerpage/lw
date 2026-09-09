<?php

namespace App\ContentAssistant\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ProposalPolicyValidator
{
    /**
     * @return array<int, string>
     */
    public function validate(User $user, Model $target): array
    {
        $policy = policy($target);

        if (! $policy || ! $user->can('update', $target)) {
            return ['You are not authorized to update this content.'];
        }

        return [];
    }
}
