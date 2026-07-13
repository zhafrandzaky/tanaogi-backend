<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $wishlists = Wishlist::with(['destination.regency', 'destination.images'])
            ->where('user_id', Auth::id())
            ->get()
            ->map(function ($wishlist) {
                // Map the response to match the frontend expectations
                $destination = $wishlist->destination;
                // Get the first image or null
                $image = $destination->images->first() ? $destination->images->first()->image_url : null;
                
                return [
                    'id' => $destination->id,
                    'wishlist_id' => $wishlist->id,
                    'title' => $destination->name,
                    'region' => $destination->regency ? $destination->regency->name : 'Sulawesi Selatan',
                    'image' => $image,
                    'note' => $wishlist->note,
                ];
            });

        return $this->success('Wishlists retrieved successfully', $wishlists);
    }

    public function toggle(Request $request): JsonResponse
    {
        \Log::info('Wishlist toggle request:', $request->all());
        
        $request->validate([
            'destination_id' => ['required', 'exists:destinations,id'],
        ]);

        $userId = Auth::id();
        $destinationId = $request->destination_id;

        $wishlist = Wishlist::where('user_id', $userId)
            ->where('destination_id', $destinationId)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            return $this->success('Destination removed from wishlist', ['status' => 'removed']);
        }

        $wishlist = Wishlist::create([
            'user_id' => $userId,
            'destination_id' => $destinationId,
        ]);

        return $this->success('Destination added to wishlist', ['status' => 'added', 'wishlist_id' => $wishlist->id], 201);
    }

    public function updateNote(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        // id could be wishlist_id or destination_id, let's support destination_id since frontend might only know destination_id
        $wishlist = Wishlist::where('user_id', Auth::id())
            ->where(function($query) use ($id) {
                $query->where('id', $id)->orWhere('destination_id', $id);
            })
            ->firstOrFail();

        $wishlist->update([
            'note' => $request->note,
        ]);

        return $this->success('Wishlist note updated successfully');
    }

    public function destroy(string $id): JsonResponse
    {
        // id could be wishlist_id or destination_id
        $wishlist = Wishlist::where('user_id', Auth::id())
            ->where(function($query) use ($id) {
                $query->where('id', $id)->orWhere('destination_id', $id);
            })
            ->firstOrFail();

        $wishlist->delete();

        return $this->success('Destination removed from wishlist');
    }
}
