<?php

namespace App\Http\Controllers\API;
use App\Models\Client;
use App\Http\Controllers\Controller;
//use Illuminate\Support\Facades\Validator;
use App\Models\Location;
use Auth;
use Illuminate\Http\Request;

class ClientController extends Controller
{
     /**
     * Return form dropdowns for clients
     */
    public function formOptions()
    {
        $location = Location::select('id', 'name')->orderBy('name')->get();
        return response()->json([
            'locations' => $location
        ]);
    }

    // Display a listing of clients
    public function index(Request $request)
    {
        //$user = Auth::user();
        $authUser = $request->user();
        $query = Client::with('location');

        //Filter by location
        // if($authUser ->role === 'admin'){
        //     if($request->filled('location_id')){
        //         $query->where('location_id', $request->location_id);
        //     }
            
        // }
        if($authUser->location && $authUser->location->name === 'Head Office'){
            if($request->filled('location_id')){
               $query->where('location_id', $request->location_id);
            }
        }else{
            $query->where('location_id', $authUser->location_id);
        }

        if (!$authUser) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        // Search functionality
        if($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
                    $q->orWhereHas('location', function($loc) use ($search){
                        $loc->where('name', 'like', "%{$search}%");
                    });
            });
        }

        //Default pagination size
        $perPage = $request->input('per_page', 10);
        $clients = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            //'clients' =>$clients,
            'data' => $clients->items(),
            'meta' => [
            'current_page' => $clients->currentPage(),
            'last_page'    => $clients->lastPage(),
            'per_page'     => $clients->perPage(),
            'total'        => $clients->total(),
            ]
        ]);
    }

    // Store a newly created client in storage
    public function store(Request $request)
    {
        $authUser = $request->user();
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'phone' => 'required|string',
            'gender' => 'required|in:male,female',
            //'is_repeating' => 'sometimes|boolean',
            //'location_id' => 'required|exists:locations,id',
        ];

        // Only admin can set location_id
        if ($authUser->location->name === 'Head Office') {
            $rules['location_id'] = 'required|exists:locations,id';
        } 

        $validated = $request->validate($rules);

        // If not admin, set location_id to user's location
        if ($authUser->location->name !== 'Head Office') {
            $validated['location_id'] = $authUser->location_id;
        }

        $client = Client::create($validated);
        return response()->json(['message' => 'Client created successfully', 'client' => $client], 200);
    }

    // Display the specified client
    public function show($id)
    {
        $client = Client::with('location')->find($id);

        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        return response()->json($client);
    }

    // Update the specified client in storage
    public function update(Request $request, $id)
    {
        $authUser = $request->user();
        $client = Client::find($id);

        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        $rules = [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:clients,email,' . $client->id,
            'phone' => 'sometimes|required|string',
            'gender' => 'sometimes|required|in:male,female',
        ];

        // Only admin can update location_id
        if ($authUser->location->name === 'Head Office') {
            $rules['location_id'] = 'sometimes|required|exists:locations,id';
        }

        $validated = $request->validate($rules);

        // If not admin, prevent changing location_id
        if ($authUser->location->name !== 'Head Office') {
            $validated['location_id'] = $authUser->location_id;
        }

        $client->update($validated);
        return response()->json(['message' => 'Client updated successfully', 'client' => $client], 200);
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
