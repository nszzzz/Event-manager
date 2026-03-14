<?php

namespace App\Http\Controllers;

use App\Models\Conversations;
use App\Models\Messages;
use App\Services\MessageAssistantService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessagesRequest;
use App\Http\Requests\UpdateMessagesRequest;
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
            new Middleware('auth:sanctum', except: ['index', 'show'])
            ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Messages::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMessagesRequest $request)
    {
        $fields = $request->validated();
        $conversation = Conversations::query()->findOrFail($fields['conversation_id']);

        Gate::authorize('modify', $conversation);

        [$userMessage, $assistantMessage] = DB::transaction(function () use ($conversation, $fields, $request) {
            $userMessage = $conversation->messages()->create([
                'sender_type' => 'user',
                'sender_user_id' => $request->user()->id,
                'content' => $fields['content'],
                'message_type' => $fields['message_type'] ?? 'text',
            ]);

            $assistantReply = $this->messageAssistantService->generateReply($fields['content']);
            $assistantMessage = $conversation->messages()->create([
                'sender_type' => 'bot',
                'sender_user_id' => null,
                'content' => $assistantReply,
                'message_type' => 'text',
            ]);

            return [$userMessage, $assistantMessage];
        });

        return response()->json([
            'message' => $userMessage,
            'assistant_message' => $assistantMessage,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Messages $message)
    {
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
