<?php

namespace App\Http\Controllers;

use App\Models\Events;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventsRequest;
use App\Http\Requests\UpdateEventsRequest;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Gate;

class EventsController extends Controller implements HasMiddleware
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
        return Events::query()
            ->with(['user:id,name,email'])
            ->orderBy('occurrence_at')
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventsRequest $request)
    {
        $fields = $request->validated();
        $event = $request->user()->events()->create($fields);
        return ['event' => $event->load('user:id,name,email')];
    }

    /**
     * Display the specified resource.
     */
    public function show(Events $event)
    {
        return $event;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventsRequest $request, Events $event)
    {
        Gate::authorize('modify', $event);
        $fields = $request->validated();

        $event->update($fields);
        return $event->load('user:id,name,email');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Events $event)
    {
        Gate::authorize('modify', $event);
        $event->delete();

        return [
            'message' => 'Event deleted successfully.'
        ];
    }
}
