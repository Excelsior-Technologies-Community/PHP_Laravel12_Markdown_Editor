<?php

use Illuminate\Support\Facades\Route;
use App\Models\Post;
use App\Http\Controllers\PostDraftController;

Route::get('/', function () {

    $posts = Post::latest()
        ->where('is_published', true)
        ->get();

    return view('posts.index', compact('posts'));
});

Route::middleware(['auth'])->group(function () {
    Route::post('/drafts', [PostDraftController::class, 'store'])->name('drafts.store');
    Route::get('/drafts/{postId}', [PostDraftController::class, 'show'])->name('drafts.show');
    Route::delete('/drafts/{postId}', [PostDraftController::class, 'destroy'])->name('drafts.destroy');
    Route::post('/drafts/{postId}/recover', [PostDraftController::class, 'recover'])->name('drafts.recover');
});