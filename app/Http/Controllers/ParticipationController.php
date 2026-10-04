<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;

class ParticipationController extends Controller
{
    // 参加する
    public function store(Request $request, Event $event)
    {
        $event->participants()->syncWithoutDetaching([$request->user()->id]);

        return back();
    }

    // 参加をやめる
    public function destroy(Request $request, Event $event)
    {
        $event->participants()->detach($request->user()->id);

        return back();
    }

    // 支払い済み/未払いを切り替える（幹事のみ）
    public function togglePaid(Request $request, Event $event, User $user)
    {
        abort_if($event->user_id !== $request->user()->id, 403);

        $participant = $event->participants()->findOrFail($user->id);

        $event->participants()->updateExistingPivot($user->id, [
            'paid' => ! $participant->pivot->paid,
        ]);

        return back();
    }
}