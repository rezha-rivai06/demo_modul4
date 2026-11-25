<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCollectionRequest;
use App\Http\Resources\UserResource;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    /**
     * Menampilkan daftar pengguna beserta koleksi media masing-masing.
     */
    public function index(): AnonymousResourceCollection
    {
        $users = User::with('media')->orderBy('id')->paginate(10);

        return UserResource::collection($users);
    }

    /**
     * Menampilkan profil pengguna beserta seluruh koleksi media miliknya.
     */
    public function show(User $user): UserResource
    {
        return new UserResource($user->load('media'));
    }

    /**
     * Custom action: Menambahkan media ke koleksi pribadi pengguna (attach).
     * Mengembalikan 409 Conflict jika media sudah ada di koleksi pengguna.
     */
    public function addToCollection(AddToCollectionRequest $request, User $user): JsonResponse
    {
        $mediaId = (int) $request->validated('media_id');

        if ($user->media()->where('media_id', $mediaId)->exists()) {
            return response()->json([
                'message' => 'Media sudah ada di koleksi pengguna',
            ], 409);
        }

        $user->media()->attach($mediaId);

        return response()->json([
            'message' => 'Media berhasil ditambahkan ke koleksi',
            'data' => new UserResource($user->load('media')),
        ], 201);
    }

    /**
     * Custom action: Menghapus media dari koleksi pengguna (detach).
     */
    public function removeFromCollection(User $user, Media $media): JsonResponse
    {
        if (! $user->media()->where('media_id', $media->id)->exists()) {
            return response()->json([
                'message' => 'Media tidak ditemukan di koleksi pengguna ini',
            ], 404);
        }

        $user->media()->detach($media->id);

        return response()->json([
            'message' => 'Media berhasil dihapus dari koleksi',
            'data' => new UserResource($user->load('media')),
        ]);
    }
}
