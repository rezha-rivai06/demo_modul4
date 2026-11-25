<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMediaRequest;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MediaController extends Controller
{
    /**
     * Menampilkan daftar seluruh media beserta relasi user yang mengoleksinya.
     * Mendukung query filter judul melalui ?search= atau ?title=.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $keyword = $request->query('search') ?? $request->query('title');

        $media = Media::with('users')
            ->search($keyword)
            ->orderBy('id')
            ->paginate(10);

        return MediaResource::collection($media);
    }

    /**
     * Menampilkan detail satu media beserta pengguna yang memasukkannya ke koleksi.
     */
    public function show(Media $media): MediaResource
    {
        return new MediaResource($media->load('users'));
    }

    /**
     * Menambahkan media baru ke dalam sistem.
     */
    public function store(StoreMediaRequest $request): JsonResponse
    {
        $media = Media::create($request->validated());

        return (new MediaResource($media))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Memperbarui data media yang sudah ada.
     */
    public function update(StoreMediaRequest $request, Media $media): MediaResource
    {
        $media->update($request->validated());

        return new MediaResource($media->fresh('users'));
    }

    /**
     * Menghapus media dari sistem.
     */
    public function destroy(Media $media): JsonResponse
    {
        $media->delete();

        return response()->json([
            'message' => 'Media berhasil dihapus',
        ]);
    }
}
