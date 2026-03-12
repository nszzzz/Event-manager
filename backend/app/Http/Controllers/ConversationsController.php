<?php

namespace App\Http\Controllers;

use App\Models\Conversations;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConversationsRequest;
use App\Http\Requests\UpdateConversationsRequest;

class ConversationsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Conversations::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConversationsRequest $request)
    {
        $fields = $request->validate();
        $conversation = Conversations::create($fields);
        return  ['conversation' => $conversation];
    }

    /**
     * Display the specified resource.
     */
    public function show(Conversations $conversations)
    {
        return $conversations;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConversationsRequest $request, Conversations $conversations)
    {
        $fields = $request->validate();

        $conversations->update($fields);
        return $conversations;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Conversations $conversations)
    {
        $conversations->delete();

        return [
            'message' => 'Conversation deleted successfully.'
        ];
    }
}
