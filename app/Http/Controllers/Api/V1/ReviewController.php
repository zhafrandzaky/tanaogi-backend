<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Models\Review;
use App\Http\Resources\ReviewResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $reviews = Review::with('user')->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Reviews fetched successfully',
            'data' => ReviewResource::collection($reviews) // Diterjemahkan oleh Resource
        ], 200);
    }

    public function store(StoreReviewRequest $request): JsonResponse
    {
        $review = $request->user()->reviews()->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully',
            'data' => $review
        ], 201);
    }
}