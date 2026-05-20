<?php

use Illuminate\Support\Facades\Route;
use App\Models\Post;

Route::get('/', function () {

    $posts = Post::latest()
        ->where('is_published', true)
        ->get();

    return view('posts.index', compact('posts'));
});