<?php

use Illuminate\Support\Facades\Route;
use App\Models\Post;

Route::get('/posts', function () {
    $posts = Post::latest()->get();
    return view('posts.index', compact('posts'));
});

Route::get('/', function () {
    return view('welcome');
});
