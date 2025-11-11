<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['restaurant'])->where('is_available', true);

        if ($request->has('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['restaurant']);

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:100',
            'preparation_time' => 'nullable|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get user's restaurant
        $user = $request->user();
        $restaurant = $user->restaurant;
        
        if (!$restaurant) {
            return response()->json([
                'success' => false,
                'message' => 'No restaurant found for this user'
            ], 404);
        }

        $data = $request->all();
        $data['restaurant_id'] = $restaurant->id;

        // Handle file upload
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        } else {
            // إذا لم يتم رفع صورة، اتركها فارغة بدلاً من استخدام صورة افتراضية
            $data['image'] = null;
        }

        $product = Product::create($data);
        $product->load(['restaurant']);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        // Check if user owns the restaurant
        if ($product->restaurant->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'category' => 'nullable|string|max:100',
            'preparation_time' => 'nullable|integer|min:1',
            'is_available' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();

        // Handle file upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);
        $product->load(['restaurant']);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product
        ]);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        // Check if user owns the restaurant
        if ($product->restaurant->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            // Delete image if exists and it's not a URL
            if ($product->image && !str_starts_with($product->image, 'http')) {
                Storage::disk('public')->delete($product->image);
            }

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting product: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getByRestaurant(Restaurant $restaurant): JsonResponse
    {
        $products = $restaurant->products()
            ->where('is_available', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function getMyProducts(Request $request): JsonResponse
    {
        $user = $request->user();
        $restaurant = $user->restaurant;
        
        if (!$restaurant) {
            return response()->json([
                'success' => false,
                'message' => 'No restaurant found for this user'
            ], 404);
        }

        $products = $restaurant->products()->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }
}
