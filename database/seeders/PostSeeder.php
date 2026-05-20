<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        Post::create([
            'title' => 'Laravel Markdown Editor',
            'slug' => 'laravel-markdown-editor',
            'content' => '# Hello World

This is markdown content.',
            'is_published' => true,
        ]);
    }
}