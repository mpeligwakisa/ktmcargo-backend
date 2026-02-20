<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Measurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeasurementController extends Controller
{
     /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data'    => Measurement::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:50',
        ]);

        $measurement = Measurement::create([
            'name' => $validated['name'],
            'unit' => $validated['unit'] ?? null,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return response()->json($measurement, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Measurement $measurement)
    {
        return response()->json($measurement);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $measurement = Measurement::find($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:50',
        ]);

        $measurement->update([
            'name' => $validated['name'],
            'unit' => $validated['unit'] ?? null,
            'updated_by' => Auth::id(),
        ]);

        return response()->json($measurement);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $measurement = Measurement::find($id);
        $measurement->delete();
        return response()->json(['message' => 'Measurement deleted successfully']);
    }
}
