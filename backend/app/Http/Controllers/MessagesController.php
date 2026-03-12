<?php

namespace App\Http\Controllers;

use App\Models\Messages;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessagesRequest;
use App\Http\Requests\UpdateMessagesRequest;

class MessagesController extends Controller
{
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
        $message = Messages::create($fields);
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
        $fields = $request->validate();

        $messages->update($fields);
        return $messages;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Messages $messages)
    {
        $messages->delete();

        return [
            'message' => 'Message deleted successfully.'
        ];
    }
}
