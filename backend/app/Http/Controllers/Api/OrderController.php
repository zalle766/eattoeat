<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Courier;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['user', 'restaurant', 'courier', 'orderItems.product']);

        // Filter by user role
        if ($request->user()->isCustomer()) {
            $query->where('user_id', $request->user()->id);
        } elseif ($request->user()->isRestaurant()) {
            $query->where('restaurant_id', $request->user()->restaurant->id);
        } elseif ($request->user()->isCourier()) {
            $query->where('courier_id', $request->user()->courier->id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    public function show(Order $order): JsonResponse
    {
        // Check authorization
        if ($request->user()->isCustomer() && $order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($request->user()->isRestaurant() && $order->restaurant_id !== $request->user()->restaurant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($request->user()->isCourier() && $order->courier_id !== $request->user()->courier->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $order->load(['user', 'restaurant', 'courier', 'orderItems.product']);

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required|exists:restaurants,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string',
            'delivery_address' => 'required|string',
            'delivery_latitude' => 'required|numeric|between:-90,90',
            'delivery_longitude' => 'required|numeric|between:-180,180',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Calculate order totals
            $subtotal = 0;
            $orderItems = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                if (!$product->is_available) {
                    throw new \Exception("Product {$product->name} is not available");
                }

                $itemTotal = $product->price * $item['quantity'];
                $subtotal += $itemTotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'notes' => $item['notes'] ?? null,
                ];
            }

            $restaurant = \App\Models\Restaurant::findOrFail($request->restaurant_id);
            $deliveryFee = $restaurant->delivery_fee;
            $tax = $subtotal * 0.1; // 10% tax
            $totalPrice = $subtotal + $deliveryFee + $tax;

            // Create order
            $order = Order::create([
                'user_id' => $request->user()->id,
                'restaurant_id' => $request->restaurant_id,
                'order_number' => 'ETO-' . str_pad(Order::count() + 1, 6, '0', STR_PAD_LEFT),
                'status' => 'pending',
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'tax' => $tax,
                'total_price' => $totalPrice,
                'delivery_address' => $request->delivery_address,
                'delivery_latitude' => $request->delivery_latitude,
                'delivery_longitude' => $request->delivery_longitude,
                'notes' => $request->notes,
            ]);

            // Create order items
            foreach ($orderItems as $item) {
                $item['order_id'] = $order->id;
                OrderItem::create($item);
            }

            // Notify restaurant
            Notification::create([
                'user_id' => $restaurant->user_id,
                'type' => 'new_order',
                'title' => 'New Order Received',
                'message' => "You have received a new order #{$order->order_number}",
                'data' => ['order_id' => $order->id]
            ]);

            DB::commit();

            $order->load(['user', 'restaurant', 'orderItems.product']);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,confirmed,preparing,ready,picked_up,delivered,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check authorization
        $canUpdate = false;
        if ($request->user()->isRestaurant() && $order->restaurant_id === $request->user()->restaurant->id) {
            $canUpdate = in_array($request->status, ['confirmed', 'preparing', 'ready', 'cancelled']);
        } elseif ($request->user()->isCourier() && $order->courier_id === $request->user()->courier->id) {
            $canUpdate = in_array($request->status, ['picked_up', 'delivered']);
        } elseif ($request->user()->isAdmin()) {
            $canUpdate = true;
        }

        if (!$canUpdate) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this order status'
            ], 403);
        }

        $oldStatus = $order->status;
        $order->update(['status' => $request->status]);

        // Set delivered_at timestamp
        if ($request->status === 'delivered') {
            $order->update(['delivered_at' => now()]);
        }

        // Notify customer
        Notification::create([
            'user_id' => $order->user_id,
            'type' => 'order_status_update',
            'title' => 'Order Status Updated',
            'message' => "Your order #{$order->order_number} status has been updated to {$request->status}",
            'data' => ['order_id' => $order->id, 'status' => $request->status]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'data' => $order
        ]);
    }

    public function getAllOrders(Request $request): JsonResponse
    {
        $orders = Order::with(['user', 'restaurant', 'courier', 'orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }
}
