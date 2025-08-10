<?php

namespace App\Http\Controllers\API;

use App\Models\people;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PeopleController extends Controller
{
    // GET /api/people
    public function index()
    {
        return response()->json(people::latest()->get());
    }

    // GET /api/people/{id}
    public function show($id)
    {
        $people = People::find($id);
        if (!$people) {
            return response()->json(['message' => 'people not found'], 404);
        }
        return response()->json($people);
    }

    // POST /api/people
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'gender' => 'required|in:male,female',
        ]);

        $validated['created_by'] = Auth::id();

        $people = People::create($validated);

        return response()->json(['message' => 'people created', 'people' => $people], 201);
    }

    // PUT /api/people/{id}
    public function update(Request $request, $id)
    {
        $people = People::find($id);
        if (!$people) {
            return response()->json(['message' => 'people not found'], 404);
        }

        $validated = $request->validate([
            'first_name' => 'sometimes|required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'sometimes|required|string|max:100',
            'gender' => 'sometimes|required|in:male,female',
        ]);

        $validated['updated_by'] = Auth::id();

        $people->update($validated);

        return response()->json(['message' => 'people updated', 'people' => $people]);
    }

    // DELETE /api/people/{id}
    public function destroy($id)
    {
        $people = People::find($id);
        if (!$people) {
            return response()->json(['message' => 'People not found'], 404);
        }

        $people->delete();

        return response()->json(['message' => 'People deleted']);
    }
}
