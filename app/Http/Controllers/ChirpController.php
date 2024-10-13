<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ChirpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 1. query from database all the chirps record
        $chirps = Chirp::latest()->paginate(); // select * from chirps limi 0, 10

        // 2. pass data to view for rendering
        // 3. then return respnose from rendered view
        return view('chirps.index', compact('chirps'));

        // return view('chirps.index', ['tweets' => $chirps]);
        // return view('chirps.index')->with('tweets', $chirps);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // validate our form input
        $validated = $request->validate([
            'message' => 'required|string|max:250',
            'message' => [
                'required',
                'string',
                'max:250'
            ],
        ]);

        // store in database through relationship
        $request->user()->chirps()->create($validated);

        // store in database without relationship
        // Chirp::create([
        //     'user_id' => auth()->user()->id,
        //     'message' => $request->message,
        // ]);

        // flash message

        // redirect
        return redirect(route('chirps.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Chirp $chirp)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Chirp $chirp)
    {
        Gate::authorize('update', $chirp);

        return view('chirps.edit', [
            'chirp' => $chirp,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Chirp $chirp)
    {
        Gate::authorize('update', $chirp);

        $validated = $request->validate([
            'message' => 'required|string|max:255',
        ]);

        $chirp->update($validated);

        return redirect(route('chirps.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Chirp $chirp)
    {
        Gate::authorize('delete', $chirp);

        $chirp->delete();

        return redirect(route('chirps.index'));
    }
}
