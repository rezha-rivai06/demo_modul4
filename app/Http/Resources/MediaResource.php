<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    /**
     * Transformasi model Media ke format respons JSON yang konsisten.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'genre' => $this->genre,
            'rating' => $this->rating,
            'release_year' => $this->release_year,
            'duration_minutes' => $this->when($this->type === 'movie', $this->duration_minutes),
            'director' => $this->when($this->type === 'movie', $this->director),
            'total_episodes' => $this->when($this->type === 'anime', $this->total_episodes),
            'studio' => $this->when($this->type === 'anime', $this->studio),
            'users' => UserResource::collection($this->whenLoaded('users')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
