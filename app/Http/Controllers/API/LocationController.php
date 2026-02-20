<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        // Return only id and name fields for dropdown
        // $query = Location::select('id', 'name', 'code', 'description')->orderBy('name')->get();

        // if (!$authUser->isSuperUser()) {
        //     $query->where('id', $authUser->location_id);
        // }

        if ($user->role && in_array($user->role->name, ['Admin', 'SuperUser', 'SuperAdmin'])) {
            $locations = Location::orderBy('name','asc')->get();
        } else {
            $locations = Location::where('id', $user->location_id)->get();
        }

        return response()->json([
            'success' => true,
            'data'    => Location::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::info('Auth user id: ', ['id' => Auth::id()]);
        \Log::info('User details: ', ['user' => $request->user()]);
        //
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:locations,name',
            'code' => 'nullable|string|max:50|unique:locations,code',
            'description' => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        $location = Location::create($validated);

        return response()->json([
            'message' => 'Location created successfully', 
            'location' => $location], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $location = Location::find($id);

        if (!$location) {
            return response()->json(['message' => 'Location not found'], 404);
        }

        return response()->json(['location' => $location], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        $location = Location::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:locations,name,' . $id,
            'code' => 'nullable|string|max:50|unique:locations,code,' . $id,
            'description' => 'nullable|string',
        ]);
        if (!$location) {
            return response()->json(['message' => 'Location not found'], 404);
        }
        $validated['updated_by'] = Auth::id();

        $location->update([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'description' => $validated['description'] ?? null,
            'updated_by' => $validated['updated_by'],
        ]);

        return response()->json(['message' => 'Location updated successfully', 'location' => $location], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $locations = Location::find($id);
        $locations->delete();
        return response()->json(['message' => 'Location deleted successfully'], 200);
    }
}
