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

        if ((int) $conversation->user_id === (int) $user->id) {
            return Response::allow();
        }

        if (
            $user->role === 'helpdesk_agent'
            && (int) $conversation->assigned_agent_id === (int) $user->id
        ) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to modify this message.');
    }
}
