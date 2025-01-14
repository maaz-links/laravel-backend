<?php

// app/Http/Controllers/SecuremirrorController.php

namespace App\Http\Controllers;

use App\Models\Securemirror;
use Illuminate\Http\Request;

class MirrorController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    
    // Display a listing of the resource.
    public function index()
    {
        $securemirrors = Securemirror::all();
        return view('securemirrors.index', compact('securemirrors'));
    }

    // Show the form for creating a new resource.
    public function create()
    {
        return view('securemirrors.create');
    }

    // Store a newly created resource in storage.
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'domain' => 'required|string|max:255',
        ]);
        //dd($request);
        //Securemirror::create($request->all());
        Securemirror::create([
            'title' => $request->title,
            'domain' => $request->domain,
        ]);
        return redirect()->route('securemirrors.index')->with('success', 'Secure Mirror created successfully.');
    }

    // Display the specified resource.
    public function show(Securemirror $securemirror)
    {
        return view('securemirrors.show', compact('securemirror'));
    }

    // Show the form for editing the specified resource.
    public function edit(Securemirror $securemirror)
    {
        return view('securemirrors.edit', compact('securemirror'));
    }

    // Update the specified resource in storage.
    public function update(Request $request, Securemirror $securemirror)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'domain' => 'required|string|max:255',
        ]);

        $securemirror->update($request->all());

        return redirect()->route('securemirrors.index')->with('success', 'Secure Mirror updated successfully.');
    }

    // Remove the specified resource from storage.
    public function destroy(Securemirror $securemirror)
    {
        $securemirror->delete();

        return redirect()->route('securemirrors.index')->with('success', 'Secure Mirror deleted successfully.');
    }
}
