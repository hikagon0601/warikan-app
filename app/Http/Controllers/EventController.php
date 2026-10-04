<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    // 一覧（検索つき）
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');

        $events = Event::query()
            ->withCount('participants')
            ->when($keyword, function ($query, $keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('title', 'like', "%{$keyword}%")
                        ->orWhere('place', 'like', "%{$keyword}%");
                });
            })
            ->orderByDesc('date')
            ->get();

        return view('events.index', compact('events', 'keyword'));
    }
    // 作成フォーム
    public function create()
    {
        return view('events.create');
    }

    // 保存
    public function store(Request $request)
    {
        $validated = $this->validateEvent($request);

        $event = $request->user()->organizedEvents()->create($validated);

        // 幹事は自動で参加者にする
        $event->participants()->attach($request->user()->id);

        return redirect()->route('events.show', $event);
    }

    // 詳細
    public function show(Event $event)
    {
        $event->load(['user', 'participants', 'comments.user']);

        return view('events.show', compact('event'));
    }

    // 編集フォーム
    public function edit(Request $request, Event $event)
    {
        $this->authorizeOrganizer($request, $event);

        return view('events.edit', compact('event'));
    }

    // 更新
    public function update(Request $request, Event $event)
    {
        $this->authorizeOrganizer($request, $event);

        $event->update($this->validateEvent($request));

        return redirect()->route('events.show', $event);
    }

    // 削除
    public function destroy(Request $request, Event $event)
    {
        $this->authorizeOrganizer($request, $event);

        $event->delete();

        return redirect()->route('events.index');
    }

    private function validateEvent(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'meeting_time' => ['nullable', 'date_format:H:i'],
            'place' => ['required', 'string', 'max:255'],
            'total_amount' => ['required', 'integer', 'min:0'],
            'memo' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    // 幹事以外は 403 エラーにする
    private function authorizeOrganizer(Request $request, Event $event): void
    {
        abort_if($event->user_id !== $request->user()->id, 403);
    }
}