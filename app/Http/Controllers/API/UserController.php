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
    public function index()
    {
        $users = User::with(['role:id,name', 'people', 'status:id,description', 'location:id,name'])
            //->select('id', 'firstName', 'middleName', 'lastName', 'email', 'status', 'staffNumber', 'role_id')
            ->orderBy('created_at', 'desc')
            ->get();

        // Add role name to each user
        $users = $users->map(function ($user) {
            return [
                'id'          => $user->id,
                'firstName'   => $user->person?->first_name?? '',
                'middleName'  => $user->person?->middle_name?? '',
                'lastName'    => $user->person?->last_name?? '',
                'gender'      => $user->person?->gender ?? '',
                'email'       => $user->email,
                'staffNumber' => $user->staff_number,
                'status'      => $user->status?->description ?? '',
                'role'        => $user->role?->name ?? '',
                'location'    => $user->location?->name ?? '', // optional future support
            ];
        });

        return response()->json($users);
    }

     // Get roles, statuses, and locations for dropdowns
     public function formOptions()
     {
        $roles = Role::select('id', 'name')->orderBy('name')->get();
        $status = Status::select('id', 'description')->orderBy('description')->get();
        $locations = Location::select('id', 'name')->orderBy('name')->get();

        return response()->json([
            'roles'     => Role::all(),
            'status'  => Status::all(),
            'locations' => Location::all()
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
            'email'          => 'required|email|users,email',
            'password'       => 'required|string|min:6',
            'gender'         => 'required|in:male,female',
            'status_id'      => 'required|exists:status,id',
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
        // if ($request->hasFile('photo')) {
        //     $data['photo'] = $request->file('photo')->store('users', 'public');
        // }

        // Create People record first
        $people = People::create([
            'first_name' => $data['firstName'],
            'middle_name' => $data['middleName']?? null,
            'last_name' => $data['lastName'],
            'gender' => $data['gender'],
        ]);

        // Assign people_id to user data
        $user = User::create([
            'email'         => $data['email'],
            'password'      => Hash::make($data['password']),
            'status_id'     => $data['status_id'],
            'people_id'     => $data['people_id'],
            'mobile'        => $data['mobile'] ?? null,
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
        $user = User::with('person')->findOrFail($id);

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

        // Update People
        if ($user->person) {
            $user->person->update([
                'first_name'  => $data['firstName'] ?? $user->person->first_name,
                'middle_name' => $data['middleName'] ?? $user->person->middle_name,
                'last_name'   => $data['lastName'] ?? $user->person->last_name,
                'gender'      => $data['gender'] ?? $user->person->gender,
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
     * List all roles for dropdown selection.
     */
    public function getRoles()
    {
        $roles = Role::select('id', 'name')->get();
        return response()->json($roles);
    }
}
