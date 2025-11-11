<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class RestaurantController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Restaurant::with(['user', 'products'])
                ->where('is_active', true);

            // Search by name
            if ($request->has('search') && $request->search) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            // Filter by category
            if ($request->has('category') && $request->category) {
                $query->whereHas('products', function ($q) use ($request) {
                    $q->where('category', $request->category);
                });
            }

            $restaurants = $query->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $restaurants
            ]);
        } catch (\Exception $e) {
            \Log::error('Restaurant index error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching restaurants',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Restaurant $restaurant): JsonResponse
    {
        $restaurant->load(['user', 'products', 'reviews.user']);

        return response()->json([
            'success' => true,
            'data' => $restaurant
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'opening_time' => 'nullable|date_format:H:i',
            'closing_time' => 'nullable|date_format:H:i',
            'delivery_fee' => 'nullable|numeric|min:0',
            'delivery_radius' => 'nullable|integer|min:1',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['user_id'] = $request->user()->id;

        // Handle file uploads
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('restaurants/logos', 'public');
        }

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('restaurants/covers', 'public');
        }

        $restaurant = Restaurant::create($data);
        $restaurant->load(['user', 'products']);

        return response()->json([
            'success' => true,
            'message' => 'Restaurant created successfully',
            'data' => $restaurant
        ], 201);
    }

    public function update(Request $request, Restaurant $restaurant): JsonResponse
    {
        // Check if user owns this restaurant
        if ($restaurant->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'sometimes|required|string',
            'latitude' => 'sometimes|required|numeric|between:-90,90',
            'longitude' => 'sometimes|required|numeric|between:-180,180',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'opening_time' => 'nullable|date_format:H:i',
            'closing_time' => 'nullable|date_format:H:i',
            'delivery_fee' => 'nullable|numeric|min:0',
            'delivery_radius' => 'nullable|integer|min:1',
            'is_open' => 'nullable|boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();

        // Handle file uploads
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($restaurant->logo) {
                Storage::disk('public')->delete($restaurant->logo);
            }
            $data['logo'] = $request->file('logo')->store('restaurants/logos', 'public');
        }

        if ($request->hasFile('cover_image')) {
            // Delete old cover image
            if ($restaurant->cover_image) {
                Storage::disk('public')->delete($restaurant->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('restaurants/covers', 'public');
        }

        $restaurant->update($data);
        $restaurant->load(['user', 'products']);

        return response()->json([
            'success' => true,
            'message' => 'Restaurant updated successfully',
            'data' => $restaurant
        ]);
    }

    public function destroy(Request $request, Restaurant $restaurant): JsonResponse
    {
        // Check if user owns this restaurant
        if ($restaurant->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $restaurant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Restaurant deleted successfully'
        ]);
    }

    public function getAllRestaurants(Request $request): JsonResponse
    {
        $restaurants = Restaurant::with(['user', 'products'])
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $restaurants
        ]);
    }
}
