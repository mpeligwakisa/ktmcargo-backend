<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Role;
use App\Models\Location;
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
    public function index(Request $request)
    {
        $authUser = $request->user();

        $query = User::with(['role',  'location', 'status', 'people']);
            
        // Apply location filtering if user is not super admin
        // if ($request->user()->role->name !== 'Admin') {
        //     $query->where('location_id', $request->user()->location_id);
        // }

        // 🔒 Restrict normal users to their own location
        if (!$authUser->role || strtolower($authUser->role->name) !== 'admin') {
            $query->where('location_id', $authUser->location_id);
        }

        $users = $query->get();

        // Add role name to each user
        $users = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'email' => $user->email,
                'phone' => $user->phone,

                'people' => $user->people ? [
                    'first_name'  => $user->people->first_name,
                    'middle_name' => $user->people->middle_name,
                    'last_name'   => $user->people->last_name,
                    'gender'      => $user->people->gender,
            ] : null,

                // Nested role object
                'role' => $user->role ? [
                    'id' => $user->role->id,
                    'name' => $user->role->name,
                    'permissions' => $user->role->permissions ?? [],
                ] : null,

                // Nested location object
                'location' => $user->location ? [
                    'id' => $user->location->id,
                    'name' => $user->location->name,
                ] : null,

                // Nested status object
                'status' => $user->status ? [
                    'id' => $user->status->id,
                    'description' => $user->status->description,
                ] : null,

                // 'people'      => $user->people
                // ? trim("{$user->people->first_name} {$user->people->middle_name} {$user->people->last_name}")
                // : null, // ✅ fix
            ];
        });

        return response()->json($users);
    }

     // Get role, status, and location for dropdowns
     public function formOptions()
     {
        $roles = Role::select('id', 'name')->orderBy('name')->get();
        $status = Status::select('id', 'description')->orderBy('description')->get();
        $locations = Location::select('id', 'name')->orderBy('name')->get();

        return response()->json([
            'role'     => Role::all(),
            'status'  => Status::all(),
            'location' => Location::all()
        ]);
     }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name'      => 'required|string|max:255',
            'middle_name'     => 'nullable|string|max:255',
            'last_name'       => 'required|string|max:255',
            'email'          => 'required|email|email',
            'password'       => 'required|string|min:6',
            'gender'         => 'required|in:male,female',
            'status_id'      => 'required|exists:status,id',
            //'staffNumber'    => 'nullable|string|max:100',
            'phone'         => 'nullable|string|max:20',
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
        // if ($request->hasFile('photo')) {
        //     $data['photo'] = $request->file('photo')->store('users', 'public');
        // }

        // Create People record first
        $people = People::create([
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name']?? null,
            'last_name' => $data['last_name'],
            'gender' => $data['gender'],
        ]);

        // Assign people_id to user data
        $user = User::create([
            'email'         => $data['email'],
            'password'      => Hash::make($data['password']),
            'status_id'     => $data['status_id'],
            'people_id'     => $people->id,
            'phone'        => $data['phone'] ?? null,
            'role_id'       => $data['role_id'],
            'location_id'   => $data['location_id'],

        ]);

        return response()->json(['message' => 'User created successfully', 'user' => $user], 200);
    }

    /**
     * Update user by ID.
     */
    public function update(Request $request, $id)
    {
        $user = User::with('people')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'first_name'      => 'sometimes|required|string|max:255',
            'middle_name'     => 'nullable|string|max:255',
            'last_name'       => 'sometimes|required|string|max:255',
            'email'          => 'sometimes|required|email|unique:email,',
            'password'       => 'nullable|string|min:6',
            'status'         => 'nullable|in:Active,Inactive',
            // 'staffNumber'    => 'nullable|string|max:100',
            'phone'         => 'nullable|string|max:20',
            // 'personalCode'   => 'nullable|string|max:50',
            'role_id'        => 'nullable|exists:roles,id',
            'location_id'    => 'nullable|exists:locations,id',
            'permissions'    => 'nullable|array',
            'stations'       => 'nullable|array',
            'photo'          => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Update People
        if ($user->people) {
            $user->people->update([
                'first_name'  => $data['first_name'] ?? $user->people->first_name,
                'middle_name' => $data['middle_name'] ?? $user->people->middle_name,
                'last_name'   => $data['last_name'] ?? $user->people->last_name,
                'gender'      => $data['gender'] ?? $user->people->gender,
            ]);
        }

        // Update password data
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
     * List all role for dropdown selection.
     */
    public function getRole()
    {
        $role = Role::select('id', 'name')->get();
        return response()->json($role);
    }
}
