<?php

namespace App\Policies;

use App\Models\Conversations;
use Illuminate\Auth\Access\Response;
use App\Models\Messages;

class MessagesPolicy
{
    public function modify(Conversations $conversations, Messages $messages): Response
    {
         return $conversations->id === $messages->conversation_id
             ? Response::allow()
             : Response::deny('You do not own this message.');
    }
}
