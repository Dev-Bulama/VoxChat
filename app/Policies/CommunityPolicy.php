<?php

namespace App\Policies;

use App\Models\Community;
use App\Models\User;

class CommunityPolicy
{
    public function update(User $user, Community $community): bool
    {
        return $community->members()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();
    }

    public function delete(User $user, Community $community): bool
    {
        return $community->members()
            ->where('user_id', $user->id)
            ->where('role', 'owner')
            ->exists();
    }
}
