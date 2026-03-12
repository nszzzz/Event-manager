<?php

namespace App\Http\Controllers;

use App\Models\Faq_entries;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFaq_entriesRequest;
use App\Http\Requests\UpdateFaq_entriesRequest;

class FaqEntriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Faq_entries::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFaq_entriesRequest $request)
    {
        $fields = $request->validate();
        $faq_entry = Faq_entries::create($fields);
        return  ['faq_entry' => $faq_entry];
    }

    /**
     * Display the specified resource.
     */
    public function show(Faq_entries $faq_entries)
    {
        return $faq_entries;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFaq_entriesRequest $request, Faq_entries $faq_entries)
    {
        $fields = $request->validate();

        $faq_entries->update($fields);
        return $faq_entries;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Faq_entries $faq_entries)
    {
        $faq_entries->delete();

        return [
            'faq_entry' => 'Faq entry deleted successfully.'
        ];
    }
}
