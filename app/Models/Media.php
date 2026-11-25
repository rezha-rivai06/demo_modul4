<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Media extends Model
{
    /**
     * Eksplisit definisikan nama tabel karena bentuk jamak kata 'media'
     * sering ambigu bagi pluralizer otomatis.
     */
    protected $table = 'media';

    protected $fillable = [
        'title',
        'type',
        'genre',
        'rating',
        'release_year',
        'duration_minutes',
        'director',
        'total_episodes',
        'studio',
    ];

    /**
     * Casting tipe data otomatis saat dibaca dari database.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'float',
            'release_year' => 'integer',
            'duration_minutes' => 'integer',
            'total_episodes' => 'integer',
        ];
    }

    /**
     * Relasi Many-to-Many: satu Media dapat dikoleksi oleh banyak User.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Query scope untuk memfilter media berdasarkan judul (case-insensitive di MySQL).
     */
    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (blank($keyword)) {
            return $query;
        }

        return $query->where('title', 'like', '%' . trim($keyword) . '%');
    }
}
