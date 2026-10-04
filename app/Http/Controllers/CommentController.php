<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Event;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // コメントを投稿
    public function store(Request $request, Event $event)
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $event->comments()->create([
            'body' => $validated['body'],
            'user_id' => $request->user()->id,
        ]);

        return back();
    }

    // 自分のコメントを削除
    public function destroy(Request $request, Event $event, Comment $comment)
    {
        abort_if($comment->user_id !== $request->user()->id, 403);

        $comment->delete();

        return back();
    }
}