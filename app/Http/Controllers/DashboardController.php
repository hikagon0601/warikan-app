<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // マイページ
    public function __invoke(Request $request)
    {
        $user = $request->user();

        // 参加予定（今日以降・日付が近い順）
        $upcomingEvents = $user->joinedEvents()
            ->withCount('participants')
            ->where('date', '>=', today())
            ->orderBy('date')
            ->get();

        // 次の飲み会（参加予定の先頭）
        $nextEvent = $upcomingEvents->first();

        // 未払い（過去の会も、幹事の会も含める）
        $unpaidEvents = $user->joinedEvents()
            ->withCount('participants')
            ->wherePivot('paid', false)
            ->get();

        $unpaidTotal = $unpaidEvents->sum(
            fn (Event $event) => Event::splitEvenly($event->total_amount, $event->participants_count)
        );

        // 幹事をする会（今日以降）
        $organizingCount = $user->organizedEvents()
            ->where('date', '>=', today())
            ->count();

        return view('dashboard', compact(
            'upcomingEvents',
            'nextEvent',
            'unpaidEvents',
            'unpaidTotal',
            'organizingCount',
        ));
    }
}