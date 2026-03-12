<?php

namespace App\Http\Controllers;

use App\Models\Conversations;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConversationsRequest;
use App\Http\Requests\UpdateConversationsRequest;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Gate;

class ConversationsController extends Controller implements HasMiddleware
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
        return Conversations::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConversationsRequest $request)
    {
        $fields = $request->validate();
        $conversation = $request->user()->conversations()->create($fields);
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
        Gate::authorize('modify', $conversations);
        $fields = $request->validate();

        $conversations->update($fields);
        return $conversations;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Conversations $conversations)
    {
        Gate::authorize('modify', $conversations);
        $conversations->delete();

        return [
            'message' => 'Conversation deleted successfully.'
        ];
    }
}
