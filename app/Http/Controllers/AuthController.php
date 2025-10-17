<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PasswordResetOtp;
use App\Mail\PasswordResetOtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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

    /**
     * Update user profile
     * Takes: first_name, last_name (optional fields)
     * Requires authentication
     */
    public function updateProfile(Request $request)
    {
        Log::info('Profile update attempt', ['user_id' => $request->user()->id]);
        
        try {
            // Validate the incoming data
            $validator = Validator::make($request->all(), [
                'first_name' => 'sometimes|string|max:255',
                'last_name' => 'sometimes|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            
            // Update only provided fields
            if ($request->has('first_name')) {
                $user->first_name = $request->first_name;
            }
            
            if ($request->has('last_name')) {
                $user->last_name = $request->last_name;
            }
            
            // Update the full name field
            if ($request->has('first_name') || $request->has('last_name')) {
                $user->name = $user->first_name . ' ' . $user->last_name;
            }
            
            $user->save();
            
            Log::info('Profile updated successfully', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Profile update failed', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating profile'
            ], 500);
        }
    }

    /**
     * Request password reset (Forgot Password)
     * Generates OTP and sends it via email
     * Takes: email
     */
    public function forgotPassword(Request $request)
    {
        Log::info('Forgot password request', ['email' => $request->email]);
        
        try {
            // Validate email
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::where('email', $request->email)->first();
            
            // Delete any existing OTPs for this email
            PasswordResetOtp::where('email', $request->email)->delete();
            
            // Generate 6-digit OTP
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Store OTP in database (expires in 10 minutes)
            PasswordResetOtp::create([
                'email' => $request->email,
                'otp' => Hash::make($otp), // Hash the OTP for security
                'expires_at' => now()->addMinutes(10),
            ]);
            
            // Send OTP via email
            Mail::to($request->email)->send(new PasswordResetOtpMail($otp, $user->first_name));
            
            Log::info('Password reset OTP sent', ['email' => $request->email]);

            return response()->json([
                'success' => true,
                'message' => 'OTP has been sent to your email',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Forgot password failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request'
            ], 500);
        }
    }

    /**
     * Reset password using OTP
     * Takes: email, otp, password, password_confirmation
     */
    public function resetPassword(Request $request)
    {
        Log::info('Password reset attempt', ['email' => $request->email]);
        
        try {
            // Validate the incoming data
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
                'otp' => 'required|string|size:6',
                'password' => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Delete expired OTPs
            PasswordResetOtp::deleteExpired($request->email);
            
            // Get all valid OTPs for this email
            $otpRecords = PasswordResetOtp::where('email', $request->email)
                ->where('expires_at', '>', now())
                ->get();
            
            if ($otpRecords->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'OTP has expired or does not exist'
                ], 400);
            }
            
            // Check if any OTP matches
            $validOtp = false;
            foreach ($otpRecords as $record) {
                if (Hash::check($request->otp, $record->otp)) {
                    $validOtp = true;
                    break;
                }
            }
            
            if (!$validOtp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid OTP'
                ], 400);
            }
            
            // Update user password
            $user = User::where('email', $request->email)->first();
            $user->password = Hash::make($request->password);
            $user->save();
            
            // Delete used OTP
            PasswordResetOtp::where('email', $request->email)->delete();
            
            // Revoke all existing tokens for security
            $user->tokens()->delete();
            
            Log::info('Password reset successful', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Password has been reset successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Password reset failed', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while resetting password'
            ], 500);
        }
    }

    /**
     * Get user profile
     * Returns current authenticated user's information
     */
    public function getProfile(Request $request)
    {
        try {
            $user = $request->user();
            
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get profile failed', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching profile'
            ], 500);
        }
    }

    /**
     * Logout user
     * Revokes current token
     */
    public function logout(Request $request)
    {
        try {
            // Revoke the current user's token
            $request->user()->currentAccessToken()->delete();
            
            Log::info('User logged out', ['user_id' => $request->user()->id]);

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Logout failed', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while logging out'
            ], 500);
        }
    }
}
