<?php

namespace App\Http\Controllers;

use App\Models\Expirationduration;
use Illuminate\Http\Request;

class ExpirationDurationController extends Controller
{
    protected $UNLIMITED_CONSTANT;
        
    public function __construct()
    {
        $this->middleware('auth');
        $this->UNLIMITED_CONSTANT = config('duration.unlimited') ?: 518400000;
    }
    // Display a listing of the expiration durations
    // public function index()
    // {
    //     $expirationDurations = Expirationduration::orderBy('duration')->get();
    //     return view('expirationdurations.index', compact('expirationDurations'));
    // }
    public function index()
    {
        $expirationDurations = Expirationduration::orderBy('duration')->get();
        //$expirationDurations =
        $unlimitstatus = 0;
        $unlimit = $expirationDurations->firstWhere('duration', $this->UNLIMITED_CONSTANT);
        if($unlimit){
            $unlimitstatus = 1;
        }
        $expirationDurations = $expirationDurations->reject(fn($item) => $item->duration == $this->UNLIMITED_CONSTANT);
        return view('expirationdurations.index', compact('expirationDurations','unlimitstatus'));
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
            //'title' => 'required|string|max:255',
            'duration' => 'required|integer|min:1|max:5256000', // Ensure duration is positive
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
            //'title' => 'required|string|max:255',
            'duration' => 'required|integer|min:1|max:5256000', // Ensure duration is positive
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

    public function unlimitedChange(){
        $unlimit = Expirationduration::where('duration',$this->UNLIMITED_CONSTANT)->first();
        if($unlimit){
            $unlimit->delete();
        }else{
            Expirationduration::create([
                'duration' => $this->UNLIMITED_CONSTANT,
            ]);
        }
        return redirect()->route('expirationdurations.index');
    }
}