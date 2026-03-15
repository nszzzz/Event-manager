<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Conversations;
use App\Models\User;

class ConversationsPolicy
{
    public function view(User $user, Conversations $conversation): Response
    {
        if ((int) $user->id === (int) $conversation->user_id) {
            return Response::allow();
        }

        if (
            $user->role === 'helpdesk_agent'
            && (
                (int) $conversation->assigned_agent_id === (int) $user->id
                || $conversation->status === Conversations::STATUS_WAITING_FOR_AGENT
            )
        ) {
            return Response::allow();
        }

        return Response::deny('You do not have access to this conversation.');
    }

    public function modify(User $user, Conversations $conversations): Response
    {
        return (int) $user->id === (int) $conversations->user_id
            ? Response::allow()
            : Response::deny('You do not own this conversation.');
    }

    public function sendMessage(User $user, Conversations $conversation): Response
    {
        if ($conversation->status === Conversations::STATUS_CLOSED) {
            return Response::deny('This conversation is closed.');
        }

        if ((int) $user->id === (int) $conversation->user_id) {
            return Response::allow();
        }

        if (
            $user->role === 'helpdesk_agent'
            && (int) $conversation->assigned_agent_id === (int) $user->id
            && $conversation->status === Conversations::STATUS_AGENT_ACTIVE
        ) {
            return Response::allow();
        }

        return Response::deny('You cannot send messages in this conversation.');
    }

    public function requestAgent(User $user, Conversations $conversation): Response
    {
        if ((int) $user->id !== (int) $conversation->user_id) {
            return Response::deny('Only the conversation owner can request an agent.');
        }

        if ($conversation->status === Conversations::STATUS_CLOSED) {
            return Response::deny('This conversation is closed.');
        }

        return Response::allow();
    }

    public function resolve(User $user, Conversations $conversation): Response
    {
        if ((int) $user->id !== (int) $conversation->user_id) {
            return Response::deny('Only the conversation owner can resolve this conversation.');
        }

        if ($conversation->status === Conversations::STATUS_CLOSED) {
            return Response::deny('This conversation is already closed.');
        }

        if ($conversation->status === Conversations::STATUS_AGENT_ACTIVE) {
            return Response::deny('An assigned agent must close this conversation.');
        }

        return Response::allow();
    }

    public function accept(User $user, Conversations $conversation): Response
    {
        if ($user->role !== 'helpdesk_agent') {
            return Response::deny('Only helpdesk agents can accept conversations.');
        }

        if ($conversation->status === Conversations::STATUS_CLOSED) {
            return Response::deny('This conversation is closed.');
        }

        if (
            $conversation->status !== Conversations::STATUS_WAITING_FOR_AGENT
            && (int) $conversation->assigned_agent_id !== (int) $user->id
        ) {
            return Response::deny('This conversation is not waiting for an agent.');
        }

        return Response::allow();
    }

    public function close(User $user, Conversations $conversation): Response
    {
        if ($user->role !== 'helpdesk_agent') {
            return Response::deny('Only helpdesk agents can close conversations.');
        }

        if ($conversation->status === Conversations::STATUS_CLOSED) {
            return Response::deny('This conversation is already closed.');
        }

        if ((int) $conversation->assigned_agent_id !== (int) $user->id) {
            return Response::deny('Only the assigned agent can close this conversation.');
        }

        return Response::allow();
    }
}
