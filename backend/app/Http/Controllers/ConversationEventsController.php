<?php

namespace App\Http\Controllers;

use App\Models\Conversation_events;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConversation_eventsRequest;
use App\Http\Requests\UpdateConversation_eventsRequest;

class ConversationEventsController extends Controller
{
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
        $conversation_event = Conversation_events::create($fields);
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
        $fields = $request->validate();

        $conversation_events->update($fields);
        return $conversation_events;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Conversation_events $conversation_events)
    {
        $conversation_events->delete();

        return [
            'conversation_event' => 'Conversation event deleted successfully.'
        ];
    }
}
