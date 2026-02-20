<?php

namespace App\Http\Controllers\API;
use App\Models\Transport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransportController extends Controller
{
    /**
     * Display a listing of transports.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data'    => Transport::all()
        ]);
    }

    /**
     * Store a newly created transport.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $transport = Transport::create([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'created_by'  => Auth::id(),
            'updated_by'  => Auth::id(),
        ]);

        return response()->json($transport, 201);
    }

    /**
     * Display a single transport.
     */
    public function show(Transport $transport)
    {
        return response()->json($transport);
    }

    /**
     * Update the specified transport.
     */
    public function update(Request $request, $id)
    {
        $transport = Transport::find($id);
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $transport->update([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'updated_by'  => Auth::id(),
        ]);

        return response()->json($transport);
    }

    /**
     * Remove the specified transport (soft delete).
     */
    public function destroy($id)
    {
        $transport = Transport:: find($id);
        $transport->delete();

        return response()->json(['message' => 'Transport deleted successfully']);
    }
}
