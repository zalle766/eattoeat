<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Restaurant;
use App\Models\Courier;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reviewable_type' => 'required|in:restaurant,courier',
            'reviewable_id' => 'required|integer',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if reviewable exists
        $reviewable = null;
        if ($request->reviewable_type === 'restaurant') {
            $reviewable = Restaurant::find($request->reviewable_id);
        } elseif ($request->reviewable_type === 'courier') {
            $reviewable = Courier::find($request->reviewable_id);
        }

        if (!$reviewable) {
            return response()->json([
                'success' => false,
                'message' => 'Reviewable not found'
            ], 404);
        }

        // Check if user already reviewed this item
        $existingReview = Review::where('user_id', $request->user()->id)
            ->where('reviewable_type', $request->reviewable_type)
            ->where('reviewable_id', $request->reviewable_id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this item'
            ], 400);
        }

        $review = Review::create([
            'user_id' => $request->user()->id,
            'reviewable_type' => $request->reviewable_type,
            'reviewable_id' => $request->reviewable_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        // Update reviewable rating
        $this->updateReviewableRating($reviewable);

        $review->load(['user']);

        return response()->json([
            'success' => true,
            'message' => 'Review created successfully',
            'data' => $review
        ], 201);
    }

    public function getReviews(Request $request, string $reviewableType, int $reviewableId): JsonResponse
    {
        $reviews = Review::with(['user'])
            ->where('reviewable_type', $reviewableType)
            ->where('reviewable_id', $reviewableId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    private function updateReviewableRating($reviewable): void
    {
        $averageRating = $reviewable->reviews()->avg('rating');
        $totalReviews = $reviewable->reviews()->count();

        $reviewable->update([
            'rating' => round($averageRating, 2),
            'total_reviews' => $totalReviews
        ]);
    }
}
