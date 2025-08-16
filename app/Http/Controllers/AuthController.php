<?php
namespace App\Http\Controllers;
 
 use Illuminate\Http\Request;
 use Illuminate\Support\Facades\Auth;
 use App\Models\User;
 use Illuminate\Support\Facades\Hash;
 
 class AuthController extends Controller
 {
     /**
      * Handle user login and return a token
      */
     public function login(Request $request)
     {
         // Validate input fields
         try{$request->validate([
             'email' => 'required|email',
             'password' => 'required'
         ]);

        //  $user = User::with('role')->where('email', $request->email)->first();

        //  if (!$user || !Hash::check($request->password, $user->password)) {
        //      return response()->json(['message' => 'Invalid credentials'], 401);
        //  }
 
         // Attempt to authenticate user
         if (!Auth::attempt($request->only('email', 'password'))) {
             return response()->json(['message' => 'Invalid credentials'], 401);
         }
 
         // Get authenticated user
         $user = User:: with(['role:id,name', 'people:id,first_name,middle_name,last_name,gender', 'location:id,name'])
                     ->where('email', $request->email)
                     ->first();
 
         // Create API token
         $token = $user->createToken('authToken')->plainTextToken;
 
         // Return user details and token
         return response()->json([
             'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role?->name?? null, // 👈 Now returns actual role 
                'status' => $user->status?->description?? null, // 👈 Now returns actual status
                'location' => $user->location?->name?? null, // 👈 Now returns actual location
            ],
             'token' => $token
         ], 200);
        }catch(\Exception $e){
            return response()->json([
                'error' => 'Something went wrong',
                'message' => $e->getMessage(),
                'trace' => $e->getTrace()
            ], 500);
        }
     } 
     /**
      * Handle user logout
      */
     public function logout(Request $request)
     {
         $request->user()->tokens()->delete(); // Revoke all tokens
         return response()->json(['message' => 'Logged out successfully'], 200);
     }
 }