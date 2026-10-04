<x-layouts.app>
    @php
        $isOrganizer = $event->user_id === auth()->id();
        $isJoined = $event->participants->contains(auth()->user());
    @endphp

    <div class="max-w-2xl space-y-8">
        {{-- 基本情報 --}}
        <div>
            <flux:heading size="xl">{{ $event->date->format('Y-m-d') }} {{ $event->title }}</flux:heading>
            <div class="mt-2 space-y-1 text-sm">
                <div>店名：{{ $event->place }}</div>
                <div>集合時間：{{ $event->meeting_time ? substr($event->meeting_time, 0, 5) : '未定' }}</div>
                <div>幹事：{{ $event->user->name }}</div>
                @if ($event->memo)
                    <div class="whitespace-pre-line">メモ：{{ $event->memo }}</div>
                @endif
            </div>

            @if ($isOrganizer)
                <div class="mt-4 flex gap-2">
                    <flux:button size="sm" :href="route('events.edit', $event)">編集</flux:button>
                    <form method="POST" action="{{ route('events.destroy', $event) }}" onsubmit="return confirm('削除しますか？')">
                        @csrf
                        @method('DELETE')
                        <flux:button size="sm" variant="danger" type="submit">削除</flux:button>
                    </form>
                </div>
            @endif
        </div>

        {{-- 参加ボタン --}}
        <div>
            @if ($isJoined)
                <form method="POST" action="{{ route('events.leave', $event) }}">
                    @csrf
                    @method('DELETE')
                    <flux:button type="submit">参加をやめる</flux:button>
                </form>
            @else
                <form method="POST" action="{{ route('events.join', $event) }}">
                    @csrf
                    <flux:button type="submit" variant="primary">参加する</flux:button>
                </form>
            @endif
        </div>

        {{-- 割り勘と参加者 --}}
        <div>
            <flux:heading size="lg">参加者（{{ $event->participants->count() }}人）</flux:heading>
            <p class="mt-1 text-sm">
                合計 {{ number_format($event->total_amount) }}円 ÷ {{ $event->participants->count() }}人
                → <span class="text-lg font-bold">一人 {{ number_format($event->perPerson()) }}円</span>
                <span class="text-zinc-500">（100円単位で切り上げ）</span>
            </p>

            <ul class="mt-3 divide-y divide-zinc-200 dark:divide-zinc-700">
                @foreach ($event->participants as $participant)
                    <li class="flex items-center justify-between py-2">
                        <span>{{ $participant->name }}</span>
                        @if ($isOrganizer)
                            <form method="POST" action="{{ route('events.paid', [$event, $participant]) }}">
                                @csrf
                                @method('PATCH')
                                <flux:button size="sm" type="submit" :variant="$participant->pivot->paid ? 'primary' : 'outline'">
                                    {{ $participant->pivot->paid ? '支払い済み' : '未払い' }}
                                </flux:button>
                            </form>
                        @else
                            <span class="text-sm {{ $participant->pivot->paid ? 'text-green-600' : 'text-zinc-500' }}">
                                {{ $participant->pivot->paid ? '支払い済み' : '未払い' }}
                            </span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- 連絡コメント --}}
        <div>
            <flux:heading size="lg">連絡</flux:heading>

            <form method="POST" action="{{ route('comments.store', $event) }}" class="mt-3 space-y-2">
                @csrf
                <flux:textarea name="body" placeholder="例：10分遅れます！" rows="2" />
                <flux:button type="submit" size="sm">送信</flux:button>
            </form>

            <ul class="mt-4 space-y-3">
                @foreach ($event->comments->sortByDesc('created_at') as $comment)
                    <li class="rounded-lg bg-zinc-100 p-3 dark:bg-zinc-700">
                        <div class="flex justify-between text-xs text-zinc-500">
                            <span>{{ $comment->user->name }}・{{ $comment->created_at->format('m/d H:i') }}</span>
                            @if ($comment->user_id === auth()->id())
                                <form method="POST" action="{{ route('comments.destroy', [$event, $comment]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="hover:text-red-500">削除</button>
                                </form>
                            @endif
                        </div>
                        <div class="mt-1 whitespace-pre-line">{{ $comment->body }}</div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</x-layouts.app>