<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Role;
use App\Models\Locations;
use App\Models\People;
use App\Models\Status;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of users with role info.
     */
    public function index()
    {
        $users = User::with(['role:id,name', 'people', 'status', 'location'])
            ->select('id', 'firstName', 'middleName', 'lastName', 'email', 'status', 'staffNumber', 'role_id')
            ->orderBy('created_at', 'desc')
            ->get();

        // Add role name to each user
        $users = $users->transform(function ($user) {
            return [
                'id' => $user->id,
                'firstName' => $people->firstName?? '',
                'middleName' => $people->middleName?? '',
                'lastName' => $people->lastName?? '',
                'email' => $user->email,
                'staffNumber' => $user->staffNumber,
                'status' => $user->status,
                'role' => $user->role?->name,
                'location' => $user->location, // optional future support
            ];
        });

        return response()->json($users);
    }

     // Get roles, statuses, and locations for dropdowns
     public function formOptions()
     {
        $roles = Role::select('id', 'name')->orderBy('name')->get();
        $statuses = Status::select('id', 'description')->orderBy('description')->get();
        $locations = Locations::select('id', 'name')->orderBy('name')->get();

        return response()->json([
            'roles'     => Role::all(),
            'statuses'  => Status::all(),
            'locations' => Locations::all()
        ]);
     }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName'      => 'required|string|max:255',
            'middleName'     => 'nullable|string|max:255',
            'lastName'       => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'password'       => 'required|string|min:6',
            'gender'         => 'required|in:male,female',
            //'confirmPassword'=> 'required|same:password',
            'status_id'         => 'required|exists:status,id',
            //'staffNumber'    => 'nullable|string|max:100',
            'mobile'         => 'nullable|string|max:20',
            //'personalCode'   => 'nullable|string|max:50',
            'role_id'        => 'required|exists:roles,id',
            'location_id'    => 'required|exists:locations,id',
            // Permissions and Stations can be stored as JSON arrays if needed
            //'photo'          => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('users', 'public');
        }

        $user = User::create([
            'email'         => $data['email'],
            'password'      => Hash::make($data['password']),
            'status_id'     => $data['status_id'],
            'people_id'     => $data['people_id'],
            'mobile'        => $data['mobile'] ?? null,
            'role_id'       => $data['role_id'],
            'location_id'   => $data['location_id'],

        ]);

        $people = People::create([
            'first_name' => $data['firstName'],
            'middle_name' => $data['middleName']?? null,
            'last_name' => $data['lastName'],
            'gender' => $data['gender'],
        ]);

        return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
    }

    /**
     * Update user by ID.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'firstName'      => 'sometimes|required|string|max:255',
            'middleName'     => 'nullable|string|max:255',
            'lastName'       => 'sometimes|required|string|max:255',
            'email'          => 'sometimes|required|email|unique:users,email,' . $user->id,
            'password'       => 'nullable|string|min:6',
            'status'         => 'nullable|in:Active,Inactive',
            'staffNumber'    => 'nullable|string|max:100',
            'mobile'         => 'nullable|string|max:20',
            'personalCode'   => 'nullable|string|max:50',
            'role_id'        => 'nullable|exists:roles,id',
            'permissions'    => 'nullable|array',
            'stations'       => 'nullable|array',
            'photo'          => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('users', 'public');
        }

        $user->update($data);

        return response()->json(['message' => 'User updated successfully', 'user' => $user]);
    }

    /**
     * Delete a user by ID.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
     * List all roles for dropdown selection.
     */
    public function getRoles()
    {
        $roles = Role::select('id', 'name')->get();
        return response()->json($roles);
    }
}
