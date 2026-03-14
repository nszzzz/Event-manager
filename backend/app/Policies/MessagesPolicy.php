<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Messages;
use App\Models\User;

class MessagesPolicy
{
    public function modify(User $user, Messages $message): Response
    {
        $conversation = $message->conversation;
        if (!$conversation) {
            return Response::deny('Conversation is missing for this message.');
        }

        return (int) $conversation->user_id === (int) $user->id
            ? Response::allow()
            : Response::deny('You do not own this message.');
    }
}
