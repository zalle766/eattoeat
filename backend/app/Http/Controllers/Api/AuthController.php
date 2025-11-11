<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => 'required|in:restaurant,courier,customer',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            // Restaurant specific fields
            'restaurant_name' => 'required_if:role,restaurant|string|max:255',
            'restaurant_description' => 'nullable|string',
            'restaurant_address' => 'nullable|string',
            'restaurant_latitude' => 'nullable|numeric|between:-90,90',
            'restaurant_longitude' => 'nullable|numeric|between:-180,180',
            'restaurant_phone' => 'nullable|string|max:20',
            'restaurant_email' => 'nullable|email',
            'delivery_fee' => 'nullable|numeric|min:0',
            'delivery_radius' => 'nullable|integer|min:1',
            'cuisine_type' => 'nullable|string|max:100',
            // Courier specific fields
            'license_number' => 'required_if:role,courier|string|max:50',
            'id_number' => 'required_if:role,courier|string|max:50',
            'vehicle_type' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $userData = $request->only([
            'name', 'email', 'password', 'role', 'phone', 'address', 'latitude', 'longitude'
        ]);
        $userData['password'] = Hash::make($userData['password']);
        $userData['is_active'] = true;

        $user = User::create($userData);

        // Create role-specific records
        if ($request->role === 'restaurant') {
            $restaurantData = [
                'user_id' => $user->id,
                'name' => $request->restaurant_name,
                'description' => $request->restaurant_description ?? '',
                'address' => $request->restaurant_address ?? $request->address ?? '',
                'latitude' => $request->restaurant_latitude ?? $request->latitude ?? 0,
                'longitude' => $request->restaurant_longitude ?? $request->longitude ?? 0,
                'phone' => $request->restaurant_phone ?? $request->phone ?? '',
                'email' => $request->restaurant_email ?? $request->email ?? '',
                'delivery_fee' => $request->delivery_fee ?? 15.00,
                'delivery_radius' => $request->delivery_radius ?? 10,
                'cuisine_type' => $request->cuisine_type ?? 'عام',
                'is_active' => true,
                'is_open' => true,
            ];
            $user->restaurant()->create($restaurantData);
        } elseif ($request->role === 'courier') {
            $courierData = [
                'user_id' => $user->id,
                'license_number' => $request->license_number,
                'id_number' => $request->id_number,
                'vehicle_type' => $request->vehicle_type ?? 'motorcycle',
                'is_available' => true,
                'is_verified' => false,
            ];
            $user->courier()->create($courierData);
        }

        // Load role-specific data
        if ($user->isRestaurant()) {
            $user->load('restaurant');
        } elseif ($user->isCourier()) {
            $user->load('courier');
        }

        // Create token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();
        
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Account is deactivated'
            ], 403);
        }
        
        // Load role-specific data
        if ($user->isRestaurant()) {
            $user->load('restaurant');
        } elseif ($user->isCourier()) {
            $user->load('courier');
        }

        // Create token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }
}