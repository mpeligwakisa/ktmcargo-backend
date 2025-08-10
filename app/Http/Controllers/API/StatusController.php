<?php

namespace App\Http\Controllers\API;

use App\Models\Status;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class StatusController extends Controller
{
    // GET /api/statuses
    public function index()
    {
        return response()->json(Status::all());
    }

    // GET /api/statuses/{id}
    public function show($id)
    {
        $status = Status::find($id);
        if (!$status) {
            return response()->json(['message' => 'Status not found'], 404);
        }

        return response()->json($status);
    }

    // POST /api/statuses
    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|unique:statuses|max:100',
        ]);

        $validated['created_by'] = Auth::id();

        $status = Status::create($validated);

        return response()->json(['message' => 'Status created', 'status' => $status], 201);
    }

    // PUT /api/statuses/{id}
    public function update(Request $request, $id)
    {
        $status = Status::find($id);
        if (!$status) {
            return response()->json(['message' => 'Status not found'], 404);
        }

        $validated = $request->validate([
            'description' => 'required|string|unique:statuses,description,' . $status->id,
        ]);

        $validated['updated_by'] = Auth::id();

        $status->update($validated);

        return response()->json(['message' => 'Status updated', 'status' => $status]);
    }

    // DELETE /api/statuses/{id}
    public function destroy($id)
    {
        $status = Status::find($id);
        if (!$status) {
            return response()->json(['message' => 'Status not found'], 404);
        }

        $status->delete();

        return response()->json(['message' => 'Status deleted']);
    }
}
