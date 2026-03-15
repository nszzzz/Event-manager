<?php

namespace App\Http\Controllers;

use App\Models\Conversations;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConversationsRequest;
use App\Http\Requests\UpdateConversationsRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ConversationsController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware('auth:sanctum')
            ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Conversations::query()
            ->with([
                'user:id,name,email',
                'assignedAgent:id,name,email',
            ])
            ->withCount('messages')
            ->orderByDesc('updated_at');

        if ($user->role === 'helpdesk_agent') {
            $query->where('assigned_agent_id', $user->id);
        } else {
            $query->where('user_id', $user->id);
        }

        return $query->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConversationsRequest $request)
    {
        $fields = $request->validated();

        $conversation = $request->user()->conversations()->create([
            'subject' => $fields['subject'] ?? null,
            'channel' => $fields['channel'],
            'status' => Conversations::STATUS_BOT_ACTIVE,
            'assigned_agent_id' => null,
        ]);

        return response()->json([
            'conversation' => $conversation->load([
                'user:id,name,email',
                'assignedAgent:id,name,email',
            ]),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Conversations $conversation)
    {
        Gate::authorize('view', $conversation);

        return $conversation->load([
            'user:id,name,email',
            'assignedAgent:id,name,email',
            'messages' => fn ($query) => $query->orderBy('created_at'),
        ]);
    }

    /**
     * Conversations currently waiting in the helpdesk queue.
     */
    public function queue(Request $request)
    {
        if ($request->user()->role !== 'helpdesk_agent') {
            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        }

        return Conversations::query()
            ->where('status', Conversations::STATUS_WAITING_FOR_AGENT)
            ->with([
                'user:id,name,email',
                'assignedAgent:id,name,email',
            ])
            ->withCount('messages')
            ->orderByDesc('updated_at')
            ->get();
    }

    /**
     * User requests escalation from bot to a human agent.
     */
    public function requestAgent(Request $request, Conversations $conversation)
    {
        Gate::authorize('requestAgent', $conversation);

        if ($conversation->status === Conversations::STATUS_CLOSED) {
            throw ValidationException::withMessages([
                'conversation' => ['This conversation is already closed.'],
            ]);
        }

        if (
            $conversation->status === Conversations::STATUS_AGENT_ACTIVE
            && $conversation->assigned_agent_id !== null
        ) {
            throw ValidationException::withMessages([
                'conversation' => ['A helpdesk agent is already handling this conversation.'],
            ]);
        }

        if ($conversation->status !== Conversations::STATUS_WAITING_FOR_AGENT) {
            $conversation->update([
                'status' => Conversations::STATUS_WAITING_FOR_AGENT,
                'assigned_agent_id' => null,
            ]);

            $conversation->messages()->create([
                'sender_type' => 'system',
                'sender_user_id' => null,
                'content' => 'A human agent has been requested. Please wait while someone accepts the conversation.',
                'message_type' => 'system',
            ]);
        }

        return response()->json([
            'conversation' => $conversation->fresh()->load([
                'user:id,name,email',
                'assignedAgent:id,name,email',
            ]),
        ]);
    }

    /**
     * Conversation owner resolves the conversation after receiving a useful bot answer.
     */
    public function resolve(Request $request, Conversations $conversation)
    {
        Gate::authorize('resolve', $conversation);

        $owner = $request->user();

        $resolvedConversation = DB::transaction(function () use ($conversation, $owner) {
            $lockedConversation = Conversations::query()
                ->whereKey($conversation->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedConversation->status === Conversations::STATUS_CLOSED) {
                throw ValidationException::withMessages([
                    'conversation' => ['This conversation is already closed.'],
                ]);
            }

            if ($lockedConversation->status === Conversations::STATUS_AGENT_ACTIVE) {
                throw ValidationException::withMessages([
                    'conversation' => ['This conversation is already handled by an agent.'],
                ]);
            }

            $lockedConversation->update([
                'status' => Conversations::STATUS_CLOSED,
                'closed_at' => now(),
            ]);

            $lockedConversation->messages()->create([
                'sender_type' => 'system',
                'sender_user_id' => $owner->id,
                'content' => sprintf('Conversation resolved by %s.', $owner->name),
                'message_type' => 'system',
            ]);

            return $lockedConversation->fresh();
        });

        return response()->json([
            'conversation' => $resolvedConversation->load([
                'user:id,name,email',
                'assignedAgent:id,name,email',
            ]),
        ]);
    }

    /**
     * Helpdesk agent accepts a queued conversation.
     */
    public function accept(Request $request, Conversations $conversation)
    {
        Gate::authorize('accept', $conversation);

        $agent = $request->user();

        $acceptedConversation = DB::transaction(function () use ($conversation, $agent) {
            $lockedConversation = Conversations::query()
                ->whereKey($conversation->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedConversation->status === Conversations::STATUS_CLOSED) {
                throw ValidationException::withMessages([
                    'conversation' => ['This conversation is already closed.'],
                ]);
            }

            if (
                $lockedConversation->assigned_agent_id !== null
                && (int) $lockedConversation->assigned_agent_id !== (int) $agent->id
            ) {
                throw ValidationException::withMessages([
                    'conversation' => ['This conversation has already been accepted by another agent.'],
                ]);
            }

            if (
                (int) $lockedConversation->assigned_agent_id === (int) $agent->id
                && $lockedConversation->status === Conversations::STATUS_AGENT_ACTIVE
            ) {
                return $lockedConversation;
            }

            if ($lockedConversation->status !== Conversations::STATUS_WAITING_FOR_AGENT) {
                throw ValidationException::withMessages([
                    'conversation' => ['This conversation is not waiting for an agent.'],
                ]);
            }

            $lockedConversation->update([
                'assigned_agent_id' => $agent->id,
                'status' => Conversations::STATUS_AGENT_ACTIVE,
            ]);

            $lockedConversation->messages()->create([
                'sender_type' => 'system',
                'sender_user_id' => $agent->id,
                'content' => sprintf('Conversation accepted by %s.', $agent->name),
                'message_type' => 'system',
            ]);

            return $lockedConversation->fresh();
        });

        return response()->json([
            'conversation' => $acceptedConversation->load([
                'user:id,name,email',
                'assignedAgent:id,name,email',
            ]),
        ]);
    }

    /**
     * Close an active conversation. Closed conversations cannot receive new messages.
     */
    public function close(Request $request, Conversations $conversation)
    {
        Gate::authorize('close', $conversation);

        $agent = $request->user();

        $closedConversation = DB::transaction(function () use ($conversation, $agent) {
            $lockedConversation = Conversations::query()
                ->whereKey($conversation->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedConversation->status === Conversations::STATUS_CLOSED) {
                throw ValidationException::withMessages([
                    'conversation' => ['This conversation is already closed.'],
                ]);
            }

            if ((int) $lockedConversation->assigned_agent_id !== (int) $agent->id) {
                throw ValidationException::withMessages([
                    'conversation' => ['Only the assigned agent can close this conversation.'],
                ]);
            }

            $lockedConversation->update([
                'status' => Conversations::STATUS_CLOSED,
                'closed_at' => now(),
            ]);

            $lockedConversation->messages()->create([
                'sender_type' => 'system',
                'sender_user_id' => $agent->id,
                'content' => sprintf('Conversation closed by %s.', $agent->name),
                'message_type' => 'system',
            ]);

            return $lockedConversation->fresh();
        });

        return response()->json([
            'conversation' => $closedConversation->load([
                'user:id,name,email',
                'assignedAgent:id,name,email',
            ]),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConversationsRequest $request, Conversations $conversation)
    {
        Gate::authorize('modify', $conversation);
        $fields = $request->validated();

        $conversation->update($fields);
        return $conversation->load([
            'user:id,name,email',
            'assignedAgent:id,name,email',
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Conversations $conversation)
    {
        Gate::authorize('modify', $conversation);
        $conversation->delete();

        return [
            'message' => 'Conversation deleted successfully.'
        ];
    }
}
