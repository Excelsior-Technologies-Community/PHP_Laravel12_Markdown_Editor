<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'featured_image',
        'content',
        'is_published',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            $post->slug = Str::slug($post->title);
        });
    }

    public function drafts(): HasMany
    {
        return $this->hasMany(PostDraft::class);
    }

    public function latestDraft()
    {
        return $this->hasOne(PostDraft::class)->latestOfMany();
    }
}