<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Locations;
use Illuminate\Http\Request;

class LocationsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Return only id and name fields for dropdown
        $locations = Locations::select('id', 'name')->orderBy('name')->get();

        return response()->json($locations, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:locations,name',
            'code' => 'nullable|string|max:50|unique:locations,code',
            'description' => 'nullable|string',
        ]);

        //$validated['created_by'] = Auth::id();

        $location = Locations::create($validated);

        return response()->json(['message' => 'Location created successfully', 'location' => $location], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $location = Locations::find($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $locations = Locations::find($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:locations,name,' . $id,
            'code' => 'nullable|string|max:50|unique:locations,code,' . $id,
            'description' => 'nullable|string',
        ]);

        $locations->update($validated);

        return response()->json(['message' => 'Location updated successfully', 'location' => $id]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $locations = Locations::find($id);
        $locations->delete();
        return response()->json(['message' => 'Location deleted successfully']);
    }
}
