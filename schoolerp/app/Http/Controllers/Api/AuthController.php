<?php

/**
 * =============================================================================
 * AUTHENTICATION CONTROLLER - Handles User Login/Logout
 * =============================================================================
 * 
 * This controller manages user authentication for the School ERP system.
 * It handles login, logout, and getting current user information.
 * 
 * DATABASE TABLES USED:
 * - users: Stores user accounts (Principal, HOD, Teachers, Accounts, Students)
 * - personal_access_tokens: Stores JWT tokens for API authentication
 * - roles: User roles (Principal, HOD Commerce, Teacher, etc.)
 * - permissions: What each role can do
 * 
 * HOW AUTHENTICATION WORKS:
 * 1. User sends email/password to /api/login
 * 2. System checks credentials against 'users' table
 * 3. If valid, creates JWT token and stores in 'personal_access_tokens' table
 * 4. Frontend stores this token and sends it with every API request
 * 5. Laravel checks token on each request to verify user is logged in
 * 
 * FOR INTERNS:
 * - JWT = JSON Web Token (like a digital ID card)
 * - Sanctum = Laravel's built-in authentication system
 * - Middleware = Code that runs before each API request to check login
 * =============================================================================
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * USER LOGIN - POST /api/login
     * 
     * This function handles user login requests.
     * 
     * PROCESS:
     * 1. Validates email and password are provided
     * 2. Checks credentials against 'users' table in database
     * 3. If correct, creates JWT token for future API requests
     * 4. Returns user info + token to frontend
     * 
     * DATABASE QUERIES:
     * - SELECT * FROM users WHERE email = ? AND password = ?
     * - INSERT INTO personal_access_tokens (user_id, token, ...)
     * - SELECT * FROM roles WHERE user_id = ?
     */
    public function login(Request $request): JsonResponse
    {
        // Step 1: Validate input data
        // Makes sure email and password are provided and email is valid format
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Step 2: Try to authenticate user
        // Auth::attempt() checks email/password against users table
        // It automatically hashes the password and compares with stored hash
        if (Auth::attempt($request->only('email', 'password'))) {
            // Step 3: Login successful - get user and create token
            $user = Auth::user();  // Get the logged-in user from database
            
            // Create JWT token for API authentication
            // This token will be sent with every future API request
            $token = $user->createToken('auth_token')->plainTextToken;

            // Step 4: Return success response with user data and token
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $user->load('roles'),  // Include user roles (Principal, HOD, etc.)
                    'token' => $token,  // JWT token for future requests
                ]
            ]);
        }

        // Step 5: Login failed - return error
        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401);  // 401 = Unauthorized HTTP status code
    }

    /**
     * USER LOGOUT - POST /api/logout
     * 
     * This function handles user logout requests.
     * 
     * PROCESS:
     * 1. Gets the current user's JWT token
     * 2. Deletes the token from database
     * 3. User can no longer make API requests with that token
     * 
     * DATABASE QUERIES:
     * - DELETE FROM personal_access_tokens WHERE token = ?
     * 
     * NOTE: This only logs out the current device/browser.
     * User might still be logged in on other devices.
     */
    public function logout(Request $request): JsonResponse
    {
        // Delete the current JWT token from database
        // This makes the token invalid for future requests
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * GET CURRENT USER - GET /api/user
     * 
     * This function returns information about the currently logged-in user.
     * Used by frontend to check if user is still logged in and get their permissions.
     * 
     * PROCESS:
     * 1. Gets user from JWT token
     * 2. Loads their roles and permissions from database
     * 3. Returns complete user profile
     * 
     * DATABASE QUERIES:
     * - SELECT * FROM users WHERE id = ?
     * - SELECT * FROM roles WHERE user_id = ?
     * - SELECT * FROM permissions WHERE role_id = ?
     * 
     * USED BY FRONTEND FOR:
     * - Checking if user is still logged in
     * - Showing/hiding menu items based on permissions
     * - Displaying user name and role in header
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            // Get current user with their roles and permissions
            // roles.permissions = nested relationship loading
            'data' => $request->user()->load('roles.permissions')
        ]);
    }
}

/**
 * =============================================================================
 * AUTHENTICATION SUMMARY FOR INTERNS
 * =============================================================================
 * 
 * HOW LOGIN WORKS:
 * 1. User enters email/password on login page
 * 2. Frontend sends POST request to /api/login
 * 3. AuthController checks credentials against database
 * 4. If valid, creates JWT token and returns it
 * 5. Frontend stores token in localStorage or cookies
 * 6. Every future API request includes this token in headers
 * 7. Laravel middleware checks token before allowing access
 * 
 * SECURITY FEATURES:
 * - Passwords are hashed (encrypted) in database
 * - JWT tokens expire after set time
 * - Tokens can be revoked (deleted) on logout
 * - Role-based permissions control what users can do
 * 
 * TESTING:
 * - Use Postman or browser to test /api/login
 * - Check database tables to see tokens being created/deleted
 * - Try accessing protected routes without token (should get 401 error)
 * =============================================================================
 */