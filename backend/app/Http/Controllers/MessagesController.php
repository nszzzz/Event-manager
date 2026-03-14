<?php

namespace App\Http\Controllers;

use App\Models\Conversations;
use App\Models\Messages;
use App\Services\MessageAssistantService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessagesRequest;
use App\Http\Requests\UpdateMessagesRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class MessagesController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly MessageAssistantService $messageAssistantService
    ) {
    }

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
        $validated = $request->validate([
            'conversation_id' => ['required', 'integer', 'exists:conversations,id'],
        ]);

        $conversation = Conversations::query()->findOrFail($validated['conversation_id']);
        Gate::authorize('view', $conversation);

        return Messages::query()
            ->where('conversation_id', $conversation->id)
            ->with('user:id,name,email')
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMessagesRequest $request)
    {
        $fields = $request->validated();
        $conversation = Conversations::query()->findOrFail($fields['conversation_id']);

        Gate::authorize('sendMessage', $conversation);

        $currentUser = $request->user();
        $senderType = $currentUser->role === 'helpdesk_agent' ? 'agent' : 'user';
        $assistantMessage = null;
        $needsHumanHandoff = false;

        $createdMessage = DB::transaction(function () use ($conversation, $fields, $currentUser, $senderType, &$assistantMessage, &$needsHumanHandoff) {
            $primaryMessage = $conversation->messages()->create([
                'sender_type' => $senderType,
                'sender_user_id' => $currentUser->id,
                'content' => $fields['content'],
                'message_type' => $fields['message_type'] ?? 'text',
            ]);

            if (
                $senderType === 'user'
                && $conversation->status === Conversations::STATUS_BOT_ACTIVE
            ) {
                $assistantReply = $this->messageAssistantService->generateReplyResult($fields['content']);
                $needsHumanHandoff = !$assistantReply['matched'];

                $assistantMessage = $conversation->messages()->create([
                    'sender_type' => 'bot',
                    'sender_user_id' => null,
                    'content' => $assistantReply['content'],
                    'message_type' => 'text',
                ]);
            } elseif ($senderType === 'user' && $conversation->status === Conversations::STATUS_WAITING_FOR_AGENT) {
                $needsHumanHandoff = true;
            }

            return $primaryMessage;
        });

        return response()->json([
            'message' => $createdMessage,
            'assistant_message' => $assistantMessage,
            'conversation_status' => $conversation->fresh()->status,
            'needs_human_handoff' => $needsHumanHandoff,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Messages $message)
    {
        Gate::authorize('view', $message->conversation);
        return $message;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMessagesRequest $request, Messages $message)
    {
        Gate::authorize('modify', $message);
        $fields = $request->validated();

        $message->update($fields);
        return $message;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Messages $message)
    {
        Gate::authorize('modify', $message);
        $message->delete();

        return [
            'message' => 'Message deleted successfully.'
        ];
    }
}
