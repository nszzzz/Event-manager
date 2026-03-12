<?php

namespace App\Http\Controllers;

use App\Models\Conversation_events;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConversation_eventsRequest;
use App\Http\Requests\UpdateConversation_eventsRequest;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Gate;

class ConversationEventsController extends Controller implements HasMiddleware
{
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
        return Conversation_events::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConversation_eventsRequest $request)
    {
        $fields = $request->validate();
        $conversation_event = $request->conversations()->conversation_events()->create($fields);
        return  ['conversation_event' => $conversation_event];
    }

    /**
     * Display the specified resource.
     */
    public function show(Conversation_events $conversation_events)
    {
        return $conversation_events;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConversation_eventsRequest $request, Conversation_events $conversation_events)
    {
        Gate::authorize('modify', $conversation_events);
        $fields = $request->validate();

        $conversation_events->update($fields);
        return $conversation_events;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Conversation_events $conversation_events)
    {
        Gate::authorize('modify', $conversation_events);
        $conversation_events->delete();

        return [
            'conversation_event' => 'Conversation event deleted successfully.'
        ];
    }
}
