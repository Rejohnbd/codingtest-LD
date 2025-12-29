<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse('Validation failed', $validator->errors());
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
            ];

            return $this->successResponse(
                'User registered successfully',
                [
                    'user' => $userData,
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ],
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Registration failed', 500);
        }
    }

    /**
     * Login user and generate token
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse('Validation failed', $validator->errors());
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->unauthorizedResponse('Invalid credentials');
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
            ];

            return $this->successResponse(
                'Login successful',
                [
                    'user' => $userData,
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ]
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Login failed', 500);
        }
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            $user->tokens()->delete();

            return $this->successResponse('Logged out successfully 2');
        } catch (\Exception $e) {
            return $this->errorResponse('Logout failed', 500);
        }
    }
}
