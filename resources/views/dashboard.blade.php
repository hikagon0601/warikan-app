<x-layouts.app>
    <div class="max-w-4xl space-y-8">
        <flux:heading size="xl">こんにちは、{{ auth()->user()->name }}さん</flux:heading>

        {{-- 上段：数字のカード3枚 --}}
        <div class="grid gap-4 md:grid-cols-3">
            <flux:card>
                <flux:text>次の飲み会</flux:text>
                @if ($nextEvent)
                    <a href="{{ route('events.show', $nextEvent) }}" class="mt-2 block hover:underline">
                        <div class="text-2xl font-bold">{{ $nextEvent->date->format('n/j') }}</div>
                        <div class="font-semibold">{{ $nextEvent->title }}</div>
                    </a>
                    <flux:text class="mt-1">
                        {{ $nextEvent->meeting_time ? substr($nextEvent->meeting_time, 0, 5) : '時間未定' }} ・ {{ $nextEvent->place }}
                    </flux:text>
                @else
                    <div class="mt-2 text-zinc-500">予定はありません</div>
                @endif
            </flux:card>

            <flux:card>
                <flux:text>未払い</flux:text>
                <div class="mt-2 text-2xl font-bold">{{ number_format($unpaidTotal) }}円</div>
                <flux:text class="mt-1">{{ $unpaidEvents->count() }}件</flux:text>
            </flux:card>

            <flux:card>
                <flux:text>幹事をする会</flux:text>
                <div class="mt-2 text-2xl font-bold">{{ $organizingCount }}件</div>
                <flux:text class="mt-1">今日以降の会</flux:text>
            </flux:card>
        </div>

        {{-- 下段：参加予定のリスト --}}
        <div>
            <flux:heading size="lg">参加予定</flux:heading>

            <div class="mt-3 divide-y divide-zinc-200 rounded-lg border border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">
                @forelse ($upcomingEvents as $event)
                    <a href="{{ route('events.show', $event) }}"
                       class="flex items-center justify-between gap-4 p-4 hover:bg-zinc-50 dark:hover:bg-zinc-700">
                        <div>
                            <div class="font-semibold">{{ $event->date->format('n/j') }} {{ $event->title }}</div>
                            <div class="text-sm text-zinc-500">{{ $event->place }}</div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span>{{ number_format(App\Models\Event::splitEvenly($event->total_amount, $event->participants_count)) }}円</span>
                            @if ($event->pivot->paid)
                                <flux:badge color="green" size="sm">支払い済み</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">未払い</flux:badge>
                            @endif
                        </div>
                    </a>
                @empty
                    <p class="p-4 text-zinc-500">参加予定の飲み会はありません。</p>
                @endforelse
            </div>

            <div class="mt-3 text-right">
                <flux:link :href="route('events.index')" wire:navigate>飲み会一覧へ →</flux:link>
            </div>
        </div>
    </div>
</x-layouts.app>