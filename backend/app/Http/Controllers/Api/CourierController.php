<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Courier;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CourierController extends Controller
{
    public function getAvailableOrders(Request $request): JsonResponse
    {
        $courier = $request->user()->courier;
        
        if (!$courier || !$courier->is_available) {
            return response()->json([
                'success' => false,
                'message' => 'Courier is not available'
            ], 400);
        }

        $orders = Order::with(['user', 'restaurant', 'orderItems.product'])
            ->where('status', 'ready')
            ->whereNull('courier_id')
            ->whereHas('restaurant', function ($query) use ($courier) {
                // Filter by delivery radius
                $query->whereRaw("
                    (6371 * acos(cos(radians(?)) 
                    * cos(radians(latitude)) 
                    * cos(radians(longitude) - radians(?)) 
                    + sin(radians(?)) 
                    * sin(radians(latitude)))) <= ?
                ", [$courier->latitude, $courier->longitude, $courier->latitude, 10]);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    public function acceptOrder(Request $request, Order $order): JsonResponse
    {
        $courier = $request->user()->courier;
        
        if (!$courier || !$courier->is_available) {
            return response()->json([
                'success' => false,
                'message' => 'Courier is not available'
            ], 400);
        }

        if ($order->status !== 'ready' || $order->courier_id) {
            return response()->json([
                'success' => false,
                'message' => 'Order is not available for pickup'
            ], 400);
        }

        $order->update([
            'courier_id' => $courier->id,
            'status' => 'picked_up'
        ]);

        // Notify customer
        Notification::create([
            'user_id' => $order->user_id,
            'type' => 'courier_assigned',
            'title' => 'Courier Assigned',
            'message' => "Your order #{$order->order_number} has been picked up by a courier",
            'data' => ['order_id' => $order->id, 'courier_id' => $courier->id]
        ]);

        // Notify restaurant
        Notification::create([
            'user_id' => $order->restaurant->user_id,
            'type' => 'order_picked_up',
            'title' => 'Order Picked Up',
            'message' => "Order #{$order->order_number} has been picked up by courier",
            'data' => ['order_id' => $order->id, 'courier_id' => $courier->id]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order accepted successfully',
            'data' => $order
        ]);
    }

    public function updateLocation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $courier = $request->user()->courier;
        
        if (!$courier) {
            return response()->json([
                'success' => false,
                'message' => 'Courier profile not found'
            ], 404);
        }

        $courier->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully',
            'data' => $courier
        ]);
    }

    public function toggleAvailability(Request $request): JsonResponse
    {
        $courier = $request->user()->courier;
        
        if (!$courier) {
            return response()->json([
                'success' => false,
                'message' => 'Courier profile not found'
            ], 404);
        }

        $courier->update(['is_available' => !$courier->is_available]);

        return response()->json([
            'success' => true,
            'message' => 'Availability updated successfully',
            'data' => ['is_available' => $courier->is_available]
        ]);
    }

    public function getAllCouriers(Request $request): JsonResponse
    {
        $couriers = Courier::with(['user'])
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $couriers
        ]);
    }
}
