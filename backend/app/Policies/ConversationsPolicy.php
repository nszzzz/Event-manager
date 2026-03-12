<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Conversations;
use App\Models\User;

class ConversationsPolicy
{
    public function modify(User $user, Conversations $conversations): Response
    {
        return $user->id === $conversations->user_id
            ? Response::allow()
            : Response::deny('You do not own this conversation.');
    }
}
