<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RestaurantController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\CourierController;
use App\Http\Controllers\Api\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Restaurant routes (public)
Route::get('/restaurants', [RestaurantController::class, 'index']);
Route::get('/restaurants/{restaurant}', [RestaurantController::class, 'show']);
Route::get('/restaurants/{restaurant}/products', [ProductController::class, 'getByRestaurant']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User profile
    Route::get('/user', [UserController::class, 'profile']);
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::put('/user', [UserController::class, 'update']);
    Route::post('/user/avatar', [UserController::class, 'uploadAvatar']);
    
    // Restaurant management (restaurant owners only)
    Route::middleware('role:restaurant')->group(function () {
        Route::post('/restaurants', [RestaurantController::class, 'store']);
        Route::put('/restaurants/{restaurant}', [RestaurantController::class, 'update']);
        Route::delete('/restaurants/{restaurant}', [RestaurantController::class, 'destroy']);
        
        Route::get('/restaurant/products', [ProductController::class, 'getMyProducts']);
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    });
    
    // Order management
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus']);
    
    // Courier routes
    Route::middleware('role:courier')->group(function () {
        Route::get('/courier/available-orders', [CourierController::class, 'getAvailableOrders']);
        Route::post('/courier/accept-order/{order}', [CourierController::class, 'acceptOrder']);
        Route::put('/courier/update-location', [CourierController::class, 'updateLocation']);
        Route::put('/courier/toggle-availability', [CourierController::class, 'toggleAvailability']);
    });
    
    // Reviews
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/reviews/{reviewable_type}/{reviewable_id}', [ReviewController::class, 'getReviews']);
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    
    // Admin routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', [UserController::class, 'adminDashboard']);
        Route::get('/admin/users', [UserController::class, 'getAllUsers']);
        Route::put('/admin/users/{user}/toggle-status', [UserController::class, 'toggleUserStatus']);
        Route::get('/admin/orders', [OrderController::class, 'getAllOrders']);
        Route::get('/admin/restaurants', [RestaurantController::class, 'getAllRestaurants']);
        Route::get('/admin/couriers', [CourierController::class, 'getAllCouriers']);
    });
});
