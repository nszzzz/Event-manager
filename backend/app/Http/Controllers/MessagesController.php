<?php

namespace App\Http\Controllers;

use App\Models\Messages;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessagesRequest;
use App\Http\Requests\UpdateMessagesRequest;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Gate;

class MessagesController extends Controller implements HasMiddleware
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
        return Messages::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMessagesRequest $request)
    {
        $fields = $request->validate();
        $message = $request->conversation()->messages()->create($fields);
        return  ['message' => $message];
    }

    /**
     * Display the specified resource.
     */
    public function show(Messages $messages)
    {
        return $messages;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMessagesRequest $request, Messages $messages)
    {
        Gate::authorize('modify', $messages);
        $fields = $request->validate();

        $messages->update($fields);
        return $messages;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Messages $messages)
    {
        Gate::authorize('modify', $messages);
        $messages->delete();

        return [
            'message' => 'Message deleted successfully.'
        ];
    }
}
