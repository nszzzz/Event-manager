<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Events;
use App\Models\User;

class EventsPolicy
{
    public function modify(User $user, Events $events): Response
     {
         return $user->id === $events->user_id
             ? Response::allow()
             : Response::deny('You do not own this event.');
     }
}
