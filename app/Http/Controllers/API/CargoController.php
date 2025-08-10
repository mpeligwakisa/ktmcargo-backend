<?php

namespace App\Http\Controllers\API;
use App\Models\Cargo;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class CargoController extends Controller {
    public function index(Request $request) {
        return Cargo::where('location_id', $request->user()->location_id)->get();
    }

    public function store(Request $request) {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'description' => 'required',
            'weight' => 'required',
            'status' => 'required',
        ]);

        //$trackingNumber = 'CARGO-' . strtoupper(uniqid());

        // Generate a unique tracking number
        $validated['cargo_number'] = 'CARGO-'.rand(1000,9999);
        while (Cargo::where('tracking_number', $validated['cargo_number'])->exists()) {
            $validated['cargo_number'] = 'CARGO-'.rand(1000,9999);
        }
        $validated['tracking_number'] = 'TRK-'.rand(100000,999999);
        while (Cargo::where('tracking_number', $validated['tracking_number'])->exists()) {
            $validated['tracking_number'] = 'TRK-'.rand(100000,999999);
        }

        Cargo::create([
            'client_id' => $request->client_id,
            'description' => $request->description,
            'weight' => $request->weight,
            'status' => $request->status,
            //'tracking_number' => $trackingNumber,
            'location_id' => $request->user()->location_id
        ]);

        return response()->json(['message' => 'Cargo added successfully']);
    }
}
