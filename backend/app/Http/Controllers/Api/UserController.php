<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Courier;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Load role-specific data
        if ($user->isRestaurant()) {
            $user->load('restaurant');
        } elseif ($user->isCourier()) {
            $user->load('courier');
        }

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'current_password' => 'sometimes|required|string',
            'password' => 'sometimes|required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $data = $request->only(['name', 'phone', 'address', 'latitude', 'longitude']);

        // Handle password change
        if ($request->has('password')) {
            if (!$request->has('current_password') || !Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 400);
            }
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user
        ]);
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Delete old avatar
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Upload new avatar
        $avatarPath = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $avatarPath]);

        return response()->json([
            'success' => true,
            'message' => 'Avatar uploaded successfully',
            'data' => ['avatar' => $avatarPath]
        ]);
    }

    public function adminDashboard(Request $request): JsonResponse
    {
        $stats = [
            'total_users' => User::count(),
            'total_restaurants' => Restaurant::count(),
            'total_couriers' => Courier::count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
            'total_revenue' => Order::where('status', 'delivered')->sum('total_price'),
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'today_revenue' => Order::where('status', 'delivered')
                ->whereDate('created_at', today())
                ->sum('total_price'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    public function getAllUsers(Request $request): JsonResponse
    {
        $users = User::with(['restaurant', 'courier'])
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    public function toggleUserStatus(Request $request, User $user): JsonResponse
    {
        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully',
            'data' => $user
        ]);
    }
}
