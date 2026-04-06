<?php

namespace App\Http\Controllers\API;
use App\Models\Cargo;
use App\Models\Measurement;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
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

        // Append remaining_days
        $cargos->getCollection()->transform(function ($cargo) {
            $cargo->remaining_days = $this->calculateRemainingDays($cargo->eta);
            return $cargo;
        });

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
            //'unit_type' => 'required|in:KG,CBM',
            'weight' => 'nullable|numeric',
            'length' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'value' => 'nullable|numeric',
            'origin_location_id' => 'required|exists:locations,id',
            'destination_location_id' => 'required|exists:locations,id',
            'transport_id' => 'required|exists:transports,id',
            'packaging' => 'nullable|string|max:255',
            //'status' => 'required|string|max:255',
            'special_instructions' => 'nullable|string|max:255',
            'eta' => 'required|date',
        ]);

        // CBM calculation
        $validated['weight_cbm'] = $this->calculateWeightCbm($validated);

        //Auto status based on ETA
        $validated['status'] = $this->calculateStatus($validated);

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
            'data' => $cargo->load(['client', 'measurement', 'originLocation', 'destinationLocation', 'transport'])
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
            //'unit_type' => 'required|exists:measurements',
            'weight' => 'nullable|numeric',
            'length' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'value' => 'nullable|numeric',
            'origin_location_id' => 'required|exists:locations,id',
            'destination_location_id' => 'required|exists:locations,id',
            'transport_id' => 'required|exists:transports,id',
            'packaging' => 'nullable|string|max:255',
            //'status' => 'required|string|max:255',
            'special_instructions' => 'nullable|string|max:255',
            'eta' => 'nullable|date',
        ]);

        // CBM calculation
        $validated['weight_cbm'] = $this->calculateWeightCbm($validated);

        //Auto status
        $validated['status'] = $this->calculateStatus($validated);

        $validated['updated_by'] = Auth::id();

        $cargo->update($validated);

        return response()->json([
            'message' => 'Cargo updated successfully',
            'data' => $cargo->load(['client', 'measurement', 'originLocation', 'destinationLocation', 'transport']),
            'cargo' => $cargo
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

    //===========Helper Function===============

    private function calculateWeightCbm($data)
    {
        $measurement = Measurement::findOrFail($data['measurement_id']);

        if ($measurement->unit === 'KG') {
            return $data['weight'] ?? 0;
        }

        if ($data['unit_type'] === 'CBM') {
            if (!empty($data['length']) && !empty($data['width']) && !empty($data['height'])) {
                return round(($data['length'] * $data['width'] * $data['height']) / 1000, 2);
            }

            return 0;
        }

        return 0;
    }

    private function calculateRemainingDays($eta)
    {
        if (!$eta)
            return null;

        $today = Carbon::now();
        $etaData = Carbon::parse($eta);
        $diff = $today->diffInDays($etaData, false);

        if ($diff > 0)
            return $diff . "days left";
        if ($diff == 0)
            return 'Arriving today';
        return 'Overdue by' . abs($diff) . 'days';
    }

    private function calculateStatus($eta)
    {
        $today = Carbon::now();
        $etaDate = Carbon::parse($eta);

        if ($etaDate->isToday())
            return 'Arriving Today';
        if ($etaDate->isFuture())
            return 'In Transit';
        return 'Delivered';
        // if($etaDate->isYesterday()) return '';
    }
}
