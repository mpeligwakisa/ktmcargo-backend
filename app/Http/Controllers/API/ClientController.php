<?php

namespace App\Http\Controllers\API;
use App\Models\Client;
use App\Http\Controllers\Controller;
//use Illuminate\Support\Facades\Validator;
use App\Models\Locations;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Location;

class ClientController extends Controller
{
     /**
     * Return form dropdowns for clients
     */
    public function formOptions()
    {
        $locations = Locations::select('id', 'name')->orderBy('name')->get();
        return response()->json([
            'locations' => $locations
        ]);
    }

    // Display a listing of clients
    public function index(Request $request)
    {
        // $search = $request->query('search');
        // $location = $request->query('location');
        $query = Client::with('location');
         //$clients = Client::all();
        // return response()->json($clients);

        if(!empty($request->location_id) && $request->location_id !== 'All'){
            $query->where('location_id', $request->location_id);
        }

        // if ($request->filled('location') && $request->location !== 'All'){
        //     $query->where('location', $request->location);
        // }

        if($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    // ->orWhere('phone', 'like', '%' . $request->search . '%')
                    // ->orWhere('gender', 'like', '%' . $request->search . '%')
                    ->orWhere('location', 'like', '%' . $search . '%');
            });
        }

        //Default pagination size
        $perPage = $request->input('per_page', 10);
        //$clients = $query->orderBy('created_at', 'desc')->get();
        $clients = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'clients' =>$clients,
        ]);
    }

    // Store a newly created client in storage
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string',
            'gender' => 'required|in:male,female',
            'location' => 'required|string',
        ]);

        $client = Client::create($validated);
        return response()->json(['message' => 'Client created successfully', 'client' => $client], 200);
    }

    // Display the specified client
    public function show($id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        return response()->json($client);
    }

    // Update the specified client in storage
    public function update(Request $request, $id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:clients,email,' . $client->id,
            'phone' => 'sometimes|required|string',
            'gender' => 'sometimes|required|in:male,female',
            'location' => 'sometimes|required|string',
        ]);

        $client->update($validated);
        return response()->json(['message' => 'Client updated successfully', 'client' => $client], 201);
    }

    // Remove the specified client from storage
    public function destroy($id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        $client->delete();
        return response()->json(['message' => 'Client deleted successfully']);
    }
}
