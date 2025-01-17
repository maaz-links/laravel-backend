<?php

namespace App\Http\Controllers;

use App\Models\Expirationduration;
use Illuminate\Http\Request;

class ExpirationDurationController extends Controller
{
    // Display a listing of the expiration durations
    public function index()
    {
        $expirationDurations = Expirationduration::all();
        return view('expirationdurations.index', compact('expirationDurations'));
    }

    // Show the form to create a new expiration duration
    public function create()
    {
        return view('expirationdurations.create');
    }

    // Store a newly created expiration duration
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'duration' => 'required|integer|min:1', // Ensure duration is positive
        ]);

        Expirationduration::create($validated);

        return redirect()->route('expirationdurations.index');
    }

    // Show the form to edit the specified expiration duration
    public function edit(Expirationduration $id)
    {
        $expirationDuration = $id;
        return view('expirationdurations.edit', compact('expirationDuration'));
    }

    // Update the specified expiration duration
    public function update(Request $request, Expirationduration $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'duration' => 'required|integer|min:1', // Ensure duration is positive
        ]);

        $id->update($validated);

        return redirect()->route('expirationdurations.index');
    }

    // Remove the specified expiration duration
    public function destroy(Expirationduration $id)
    {
        //dd($id);
        $id->delete();
        return redirect()->route('expirationdurations.index');
    }
}