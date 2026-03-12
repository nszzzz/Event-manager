<?php

namespace App\Http\Controllers;

use App\Models\Events;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventsRequest;
use App\Http\Requests\UpdateEventsRequest;

class EventsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Events::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventsRequest $request)
    {
        $fields = $request->validate();
        $event = Events::create($fields);
        return  ['event' => $event];
    }

    /**
     * Display the specified resource.
     */
    public function show(Events $events)
    {
        return $events;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventsRequest $request, Events $events)
    {
        $fields = $request->validate();

        $events->update($fields);
        return $events;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Events $events)
    {
        $events->delete();

        return [
            'message' => 'Event deleted successfully.'
        ];
    }
}
