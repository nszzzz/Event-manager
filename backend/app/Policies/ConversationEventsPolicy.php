<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Conversation_events;
use App\Models\Conversations;

class ConversationEventsPolicy
{
    public function modify(Conversations $conversations, Conversation_events $conversationEvents): Response
    {
        return $conversations->id === $conversationEvents->user_id
            ? Response::allow()
            : Response::deny('You do not own this conversation event.');
    }
}
