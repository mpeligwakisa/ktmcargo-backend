<?php

namespace App\Http\Controllers\API;
use App\Models\Cargo;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class CargoController extends Controller
{
    // List cargos
    public function index(Request $request)
    {
        $authUser = $request->user();



        $query = Cargo::with([
            'client',
            'measurement',
            'originLocation',
            'destinationLocation',
            'transport',
            'creator',
            'updater'
        ]);

        // ✅ If user is NOT from Head Office, filter by their location
        if ($authUser->location && strtolower($authUser->location->name) !== 'head office') {
            $query->where(function ($q) use ($authUser) {
                $q->where('origin_location_id', $authUser->location_id)
                    ->orWhere('destination_location_id', $authUser->location_id);
            });
        }

        $cargos = $query->paginate(10);

        return response()->json($cargos);
    }

    // Store cargo
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'cargo_name' => 'required|string|max:255',
            'container_number' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:1',
            'measurement_id' => 'required|exists:measurements,id',
            'unit_type' => 'required|in:KG,CBM',
            'weight_cbm' => 'nullable|numeric',
            'value' => 'nullable|numeric',
            'origin_location_id' => 'required|exists:locations,id',
            'destination_location_id' => 'required|exists:locations,id',
            'transport_id' => 'required|exists:transports,id',
            'packaging' => 'nullable|string|max:255',
            'status' => 'required|string|max:255',
            'special_instructions' => 'nullable|string|max:255',
            'eta' => 'nullable|date',
        ]);

        // Generate a unique tracking number
        $validated['cargo_number'] = 'CARGO-' . rand(100000, 999999);
        while (Cargo::where('cargo_number', $validated['cargo_number'])->exists()) {
            $validated['cargo_number'] = 'CARGO-' . rand(100000, 999999);
        }
        $validated['tracking_number'] = 'TRK-' . rand(100000, 999999);
        while (Cargo::where('tracking_number', $validated['tracking_number'])->exists()) {
            $validated['tracking_number'] = 'TRK-' . rand(100000, 999999);
        }

        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        $cargo = Cargo::create($validated);

        return response()->json([
            'message' => 'Cargo created successfully',
            //'cargo' => $cargo
            'data' => $cargo->load(['client', 'originLocation', 'destinationLocation', 'transport'])
        ]);
    }

    // Show single cargo
    public function show($id)
    {
        $cargo = Cargo::with([
            'client',
            'measurement',
            'originLocation',
            'destinationLocation',
            'transport',
            'creator',
            'updater'
        ])->findOrFail($id);

        return response()->json($cargo);
    }

    // Update cargo
    public function update(Request $request, $id)
    {
        $cargo = Cargo::findOrFail($id);

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'cargo_name' => 'required|string|max:255',
            'cargo_number' => 'nullable|string|max:255',
            'container_number' => 'nullable|string|max:255',
            //'tracking_number' => 'required|string|unique:cargos,tracking_number,' . $cargo->id,
            'category' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:1',
            'measurement_id' => 'required|exists:measurements,id',
            'unit_type' => 'required|in:KG,CBM',
            'weight_cbm' => 'nullable|numeric',
            'value' => 'nullable|numeric',
            'origin_location_id' => 'required|exists:locations,id',
            'destination_location_id' => 'required|exists:locations,id',
            'transport_id' => 'required|exists:transports,id',
            'packaging' => 'nullable|string|max:255',
            'status' => 'required|string|max:255',
            'special_instructions' => 'nullable|string|max:255',
            'eta' => 'nullable|date',
        ]);

        $validated['updated_by'] = Auth::id();

        $cargo->update($validated);

        return response()->json([
            'message' => 'Cargo updated successfully',
            'data' => $cargo->load(['client', 'originLocation', 'destinationLocation', 'transport'])
            //'cargo' => $cargo
        ]);
    }

    // Delete cargo
    public function destroy($id)
    {
        $cargo = Cargo::findOrFail($id);
        $cargo->delete();

        return response()->json([
            'message' => 'Cargo deleted successfully'
        ]);
    }
}
