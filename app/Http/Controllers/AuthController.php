<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Sign up a new user
     * Takes: first_name, last_name, email, password
     */
    public function signup(Request $request)
    {
        Log::info('Signup attempt started', ['email' => $request->email]);
        
        try {
            // Validate the incoming data
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
            ]);

            // If validation fails, return errors
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6',
            ]);
            
            Log::info('Validation passed', $validated);
            
            // Create the user in the database
            $user = User::create([
                'name' => $validated['first_name'] . ' ' . $validated['last_name'], // Auto-populate name
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
            ]);
            
            Log::info('User created successfully', ['user_id' => $user->id]);
            
            // Create an authentication token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                ],
                'token' => $token,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Signup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Log in an existing user
     * Takes: email, password
     */
    public function login(Request $request)
    {
        Log::info('Login attempt', ['email' => $request->email]);
        
        try {
            // Validate the incoming data
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);

            // If validation fails, return errors
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find user by email
            $user = User::where('email', $request->email)->first();

            // Check if user exists and password is correct
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // Create an authentication token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                ],
                'token' => $token,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Login failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
