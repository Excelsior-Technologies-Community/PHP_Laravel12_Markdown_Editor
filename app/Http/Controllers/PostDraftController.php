<?php

namespace App\Http\Controllers;

use App\Models\PostDraft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostDraftController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'post_id' => 'nullable|exists:posts,id',
            'title' => 'nullable|string',
            'content' => 'nullable|string',
            'featured_image' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        $draft = PostDraft::updateOrCreate(
            [
                'post_id' => $validated['post_id'] ?? null,
                'user_id' => Auth::id(),
            ],
            [
                'title' => $validated['title'] ?? null,
                'content' => $validated['content'] ?? null,
                'featured_image' => $validated['featured_image'] ?? null,
                'metadata' => $validated['metadata'] ?? null,
                'is_active' => true,
                'last_synced_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'draft_id' => $draft->id,
            'last_synced_at' => $draft->last_synced_at,
        ]);
    }

    public function show($postId)
    {
        $draft = PostDraft::forPost($postId)
            ->forUser(Auth::id())
            ->active()
            ->recent()
            ->first();

        if (!$draft) {
            return response()->json(['success' => false, 'message' => 'No draft found'], 404);
        }

        return response()->json([
            'success' => true,
            'draft' => $draft,
        ]);
    }

    public function destroy($postId)
    {
        $draft = PostDraft::forPost($postId)
            ->forUser(Auth::id())
            ->first();

        if ($draft) {
            $draft->delete();
        }

        return response()->json(['success' => true]);
    }

    public function recover($postId)
    {
        $draft = PostDraft::forPost($postId)
            ->forUser(Auth::id())
            ->active()
            ->recent()
            ->first();

        if (!$draft) {
            return response()->json(['success' => false, 'message' => 'No draft found'], 404);
        }

        return response()->json([
            'success' => true,
            'title' => $draft->title,
            'content' => $draft->content,
            'featured_image' => $draft->featured_image,
            'metadata' => $draft->metadata,
        ]);
    }
}
